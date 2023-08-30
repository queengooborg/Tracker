<?php

namespace App\Models;

use App\Casts\JsonValue;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\Setting firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\Setting firstOrFail($columns = ['*'])
 * @method \App\Models\Setting firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\Setting firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\Setting firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\Setting updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class Setting extends Model {
	use LogsActivity;

	protected $keyType = 'string';

	protected $casts = [
		'value' => JsonValue::class,
	];

	/**
	 * Internal in-memory settings cache
	 *
	 * @var array<string, mixed>
	 */
	private static array $settingsCache = [];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['value'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	/**
	 * Stores a new value for the setting in the database and clears any cached value for it
	 */
	public function setValue(Model|array|bool|int|float|string|null $value): void {
		static::set($this->id, $value);
	}

	/**
	 * Stores a setting value in the database and clears any cached value for it
	 */
	public static function set(string $id, Model|array|bool|int|float|string|null $value): void {
		// Wipe the cache
		unset(static::$settingsCache[$id]);
		Cache::delete("setting:{$id}");

		// Update the value
		$count = static::whereId($id)->update([
			'value' => json_encode($value instanceof Model ? $value->getKey() : $value)
		]);

		// Make sure the update went through - if it didn't, it's for an unknown setting
		if ($count !== 1) throw new \ValueError("Unknown setting ID: {$id}");
	}

	/**
	 * Retrieves and caches whether the app should be in dev mode
	 */
	public static function isDevMode(): bool {
		return (bool) static::getAndCacheValue('dev-mode');
	}

	/**
	 * Retrieves and caches whether the app should be locked down
	 */
	public static function isLockedDown(): bool {
		return (bool) static::getAndCacheValue('lockdown');
	}

	/**
	 * Retrieves and caches the active event
	 */
	public static function activeEvent(): ?Event {
		return static::getAndCacheValue('active-event', fn (?string $val) => Event::find($val));
	}

	/**
	 * Retrieve the value of a setting from the in-memory cache, cache provider, or database, and cache it appropriately
	 *
	 * @param string $id
	 * @param ?callable $transformer Function to mutate the setting value
	 */
	private static function getAndCacheValue(string $id, ?callable $transformer = null): mixed {
		// Return the setting value from the in-memory cache if it exists there
		if (array_key_exists($id, static::$settingsCache)) return static::$settingsCache[$id];

		// Retrieve the setting value from the cache provider if it exists there, otherwise obtain it from the DB and cache it
		$value = Cache::remember("setting:{$id}", 60 * 5, function () use ($id, $transformer) {
			$setting = static::findOrFail($id);
			if (!$setting || !$transformer) return $setting?->value;
			return $transformer($setting->value);
		});

		// Store the setting value in the in-memory cache
		static::$settingsCache[$id] = $value;
		return $value;
	}
}
