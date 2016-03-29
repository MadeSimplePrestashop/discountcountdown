$(document).ready(function () {
    $('#globaldiscount').keyup(function () {
        $('.discountcategory').attr('placeholder', $(this).val());
    })
})