$(document).ready(function () {
    $('.clock').each(function () {
        $(this).countdown($(this).data('date-countdown'), function (event) {
            $(this).html(event.strftime($(this).data('date-format')));
        });
    })
})