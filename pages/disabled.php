<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 2/10/2019
 * Time: 3:57 AM
 */

if ($siteStatus == 0) {
    $statusClass = "siteDisabled";
    $message = "Site disabled.";
    $description = "Site is disabled for maintenance.";
} else if ($kioskAuth == 0) {
    $statusClass = "noKiosk";
    $message = "Device not authorized.";
    $description = "If you believe this is in error, please contact a staff member.";
} else if ($siteStatus == 12) {
    $statusClass = "onFire";
    $message = "SITE ON FIRE!!";
    $description = "Something has gone terribly wrong.";
}

if (!isAdmin($badgeID)) {
    logoutSession($session);
    session_unset();
    session_regenerate_id();
}
?>

    <div class="container" style="top: 5em;position: relative;">
        <div class="card">
            <div class="card-header highvis <?php echo $statusClass ?>">
                <div class="vistext"><?php echo $message ?></div>
            </div>
            <div class="card-body">
                <?php echo $description ?>
            </div>
        </div>

        <?php
        include('pages/adminFunctions.php');
        ?>
    </div>

<?php
if (isAdmin($badgeID) || isManager($badgeID)) {
    ?>
    <div class="container" style="top: 5em;position: relative;">
        <div class="card novis">
            <div class="autologout">Auto logout in <span id="lsec">60</span> <span id="gram">seconds</span>...
            </div>
        </div>
    </div>
    <script src="js/landing.js"></script>
    <script>$(document).ready(function () {
            decrementLogout();
        });
    </script>
    <?php
}
?>