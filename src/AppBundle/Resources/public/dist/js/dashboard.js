$( document ).ready(function() {
    getAjaxData();

    setInterval(function(){
        if(running === true) {
            $.post(Routing.generate('isRunning', {}), function (data) {
                if(data === 'ko'){
                    running = false;
                    stopRunning();
                }
            });
        }

        getAjaxData();
    }, interval * 1000);

    $("#start-btn").on( 'click', function (e) {
        e.preventDefault();
        var form = document.getElementById("trading-form");
        if(form.checkValidity()){
            var thresholdUsd = $("#threshold-usd").val();
            var orderValueBtc = $("#order-value-btc").val();
            var tradingTimeMinutes = $("#trading-time-minutes").val();
            var addOrSubToOrderUsd = $("#add-or-sub-to-order-usd").val();
            var maxOpenOrders = $("#max-open-orders").val();

            $.post( Routing.generate('startTrading', {thresholdUsd: thresholdUsd, orderValueBtc:orderValueBtc, tradingTimeMinutes:tradingTimeMinutes, addOrSubToOrderUsd:addOrSubToOrderUsd, maxOpenOrders:maxOpenOrders}), function( data ) {
                if(data.running === true) {
                    $("#start-btn").removeClass('btn-primary').addClass('btn-default');
                    $("#stop-btn").removeClass('btn-default').addClass('btn-danger');
                    $("#trading-since").html("Trading start time: " + data.startDate);
                    $("#max-open-orders").prop('disabled', true);
                    $("#trading-time-minutes").prop('disabled', true);
                    getAjaxData();

                    running = true;
                }
            });
        } else{
            form.reportValidity();
        }
    });

    $("#refresh-balance-btn").on('click', function() {
        $("#refresh-icon").addClass('fa-spin');
        $.post( Routing.generate('balancesFromExchanges', {}), function( data ) {
            $.post(Routing.generate('balance', {}), function (data) {
                $("#balance-table").html(data);
            });
            $.post(Routing.generate('balanceDate', {}), function (data) {
                if(data) {
                    $("#balance-date").html('Last updated: ' + data);
                } else{
                    $("#balance-date").html('');
                }
                $("#refresh-icon").removeClass('fa-spin');
            });
        });
    });

    $("#stop-btn").on( 'click', function (e) {
        e.preventDefault();
        $.post( Routing.generate('stopTrading', {}), function( data ) {
            if(data.running === false) {

                stopRunning();

                running = false;
            }
        });
    });

    $("#increase-threshold-usd").on('click', function (e) {
        var thresholdUsd = $("#threshold-usd").val();
        if(!thresholdUsd || isNaN(thresholdUsd)){
            thresholdUsd = 0;
        }
        thresholdUsd ++;
        $("#threshold-usd").val(thresholdUsd);
        postTradeParameters();
    });

    $("#decrease-threshold-usd").on('click', function (e) {
        var thresholdUsd = $("#threshold-usd").val();
        if(!thresholdUsd || isNaN(thresholdUsd)){
            thresholdUsd = 0;
        }
        thresholdUsd --;
        if(thresholdUsd <= 1){
            thresholdUsd = 1;
        }
        $("#threshold-usd").val(thresholdUsd);
        postTradeParameters();
    });

    $("#threshold-usd, #order-value-btc, #add-or-sub-to-order-usd").on('change', function (e) {
        postTradeParameters();
    });

    $("#increase-order-value-btc").on('click', function (e) {
        var orderValueBtc = $("#order-value-btc").val();
        if(!orderValueBtc || isNaN(orderValueBtc)){
            orderValueBtc = 0;
        }
        orderValueBtc  = (parseFloat(orderValueBtc) + 0.001);
        $("#order-value-btc").val(orderValueBtc);
        postTradeParameters();
    });

    $("#decrease-order-value-btc").on('click', function (e) {
        var orderValueBtc = $("#order-value-btc").val();
        if(!orderValueBtc || isNaN(orderValueBtc)){
            orderValueBtc = 0;
        }
        orderValueBtc  = (parseFloat(orderValueBtc) - 0.001);
        if(orderValueBtc <= 0.001){
            orderValueBtc = 0.001;
        }
        $("#order-value-btc").val(orderValueBtc);
        postTradeParameters();
    });

    $("#increase-add-or-sub-to-order-usd").on('click', function (e) {
        var addOrSubToOrderUsd = $("#add-or-sub-to-order-usd").val();
        if(!addOrSubToOrderUsd || isNaN(addOrSubToOrderUsd)){
            addOrSubToOrderUsd = 0;
        }
        addOrSubToOrderUsd ++;
        $("#add-or-sub-to-order-usd").val(addOrSubToOrderUsd);
        postTradeParameters();
    });

    $("#decrease-add-or-sub-to-order-usd").on('click', function (e) {
        var addOrSubToOrderUsd = $("#add-or-sub-to-order-usd").val();
        if(!addOrSubToOrderUsd || isNaN(addOrSubToOrderUsd)){
            addOrSubToOrderUsd = 0;
        }
        addOrSubToOrderUsd --;
        if(addOrSubToOrderUsd <= 0){
            addOrSubToOrderUsd = 0;
        }
        $("#add-or-sub-to-order-usd").val(addOrSubToOrderUsd);
        postTradeParameters();
    });

    $("#threshold-usd, #order-value-btc, #add-or-sub-to-order-usd").on('change', function (e) {
        postTradeParameters();
    });

    bindPlaceOrderBtn();
});

function postTradeParameters() {
    var thresholdUsd = $("#threshold-usd").val();
    var orderValueBtc = $("#order-value-btc").val();
    var addOrSubToOrderUsd = $("#add-or-sub-to-order-usd").val();
    $.post(Routing.generate('changeTradeParameters', {thresholdUsd:thresholdUsd, orderValueBtc:orderValueBtc, addOrSubToOrderUsd:addOrSubToOrderUsd}), function (data) {
        $("#threshold-usd").val(data.thresholdUsd);
        $("#order-value-btc").val(data.orderValueBtc)
        $("#add-or-sub-to-order-usd").val(data.addOrSubToOrderUsd)
    });
}

function stopRunning(){
    $("#start-btn").removeClass('btn-default').addClass('btn-primary');
    $("#stop-btn").removeClass('btn-danger').addClass('btn-default');
    $("#trading-since").html("");
    $("#trading-time-minutes").prop('disabled', false);
    $("#max-open-orders").prop('disabled', false);
    getAjaxData();
}

function getAjaxData(){
    $.post(Routing.generate('balance', {}), function (data) {
        $("#balance-table").html(data);
    });

    $.post(Routing.generate('ticker', {}), function (data) {
        $("#ticker-table").html(data);
    });

    $.post(Routing.generate('difference', {}), function (data) {
        $("#difference-table").html(data);
        bindPlaceOrderBtn();
    });

    $.post(Routing.generate('orderPair', {}), function (data) {
        $("#order-pair-table").html(data);
    });

    $.post(Routing.generate('balanceDate', {}), function (data) {
        if(data) {
            $("#balance-date").html('Last updated: ' + data);
        } else{
            $("#balance-date").html('');
        }
    });

    $.post(Routing.generate('tickerDate', {}), function (data) {
        if(data) {
            $("#ticker-date").html('Last updated: ' + data);
        } else{
            $("#ticker-date").html('');
        }
    });

    $.post(Routing.generate('differenceDate', {}), function (data) {
        if(data) {
            $("#difference-date").html('Last updated: ' + data);
        } else{
            $("#difference-date").html('');
        }
    });

    $.post(Routing.generate('orderPairDate', {}), function (data) {
        if(data) {
            $("#order-pair-date").html('Last updated: ' + data);
        } else{
            $("#order-pair-date").html('');
        }
    });
}

function bindPlaceOrderBtn(){
    $(".place-order-btn").on('click', function (e) {
        var differenceId = $(this).data('difference-id');
        $.post(Routing.generate('placeOrderPair', {differenceId:differenceId}), function (data) {
            $("#order-placed-modal-text").html(data.message);
            $("#order-placed-modal").removeClass('modal-success');
            $("#order-placed-modal").removeClass('modal-warning');
            $("#order-placed-modal").removeClass('modal-danger');
            if(data.status === 'error'){
                $("#order-placed-modal").addClass('modal-danger');
                $("#order-placed-modal-title").html('Error');
            }else if(data.status === 'warning'){
                $("#order-placed-modal").addClass('modal-warning');
                $("#order-placed-modal-title").html('Warning');
            } else{
                $("#order-placed-modal").addClass('modal-success');
                $("#order-placed-modal-title").html('Success');
            }
            $("#order-placed-modal").modal();
        });
    });
}
