export const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    showClass: {
        popup: "animate__animated animate__slideInDown animate__faster"
    }
});

export function debounce(func, wait, immediate) {
    var timeout;
    return function () {
        var context = this, args = arguments;
        var later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};

export function addRow(key, elem, data) {
    let innerTable = '';
    for (let i = 0; i < data.length; i++) {
        const t = (key && i === 0) ? 'th' : 'td';
        innerTable += '<' + t + '>' + data[i] + '</' + t + '>';
    }
    $(elem).append('<tr>' + innerTable + '</tr>');
}

export function getButtonInput(object) {
    return $(object).parent().parent().find('.form-control').val();
}

export function getButtonSelect(object) {
    return $(object).parent().parent().find('.form-select').val();
}

export function getTableKey(object) {
    return $('th:first', $(object).parents('tr')).text();
}

export async function postAction(url, data, callback) {
    $.post(url, data)
        .done(function (data) {
            if (data.code === 0) alert('Error: ' + data.msg);
            if (callback) callback(data);
            if (data.msg !== undefined) console.log(data.msg);
        }).fail(function (data) {
        	console.error(data);
        	alert('Internal error, please contact a staff member for assistance.');
    	});
}