(function ($, window, document) {

    //dom ready
    $(function () {

        //area manager store change listener
        $("#store", ".area-manager-store-selection").change(function () {
            //refresh the page if a store is selected
            var store_selected = $(this).val();
            if (store_selected != '') {
                window.location.href = window.location.pathname + "?" + $.param({'store-selected': store_selected});
            }
        });

    });

}(window.jQuery, window, document));