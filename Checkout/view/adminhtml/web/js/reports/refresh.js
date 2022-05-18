require([
    'jquery'
], function ($) {
    function submitRefresh(url) {
        var params = $.param($('.entry-edit.form-inline :input'));

        location.href = url + 'filters/' + encodeURIComponent(params);
    }

    function checkPeriodVisibility() {
        if ($('#checkout_reports_date_range').val() === "0") {
            $('#checkout_reports_date_from, #checkout_reports_date_to').parent().show().find('*').show();
        } else {
            $('#checkout_reports_date_from, #checkout_reports_date_to').parent().hide();
        }
    }

    $('#checkout_reports_date_range').on('change', checkPeriodVisibility);
    checkPeriodVisibility();
    window.submitRefresh = submitRefresh;
    window.checkPeriodVisibility = checkPeriodVisibility;
});