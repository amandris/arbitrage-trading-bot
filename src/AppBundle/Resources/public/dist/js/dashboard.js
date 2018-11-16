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
            var orderValueUsd = $("#order-value-usd").val();
            var tradingTimeMinutes = $("#trading-time-minutes").val();
            var addOrSubToOrderUsd = $("#add-or-sub-to-order-usd").val();
            var maxOpenOrders = $("#max-open-orders").val();

            $.post( Routing.generate('startTrading', {thresholdUsd: thresholdUsd, orderValueUsd:orderValueUsd, tradingTimeMinutes:tradingTimeMinutes, addOrSubToOrderUsd:addOrSubToOrderUsd, maxOpenOrders:maxOpenOrders}), function( data ) {
                if(data.running === true) {
                    $("#start-btn").removeClass('btn-primary').addClass('btn-default');
                    $("#stop-btn").removeClass('btn-default').addClass('btn-danger');
                    $("#trading-since").html("Trading start time: " + data.startDate);
                    $("#max-open-orders").prop('disabled', true);
                    $("#trading-time-minutes").prop('disabled', true);

                    running = true;
                }
            });
        } else{
            form.reportValidity();
        }
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

    $("#increase-order-value-usd").on('click', function (e) {
        var orderValueUsd = $("#order-value-usd").val();
        if(!orderValueUsd || isNaN(orderValueUsd)){
            orderValueUsd = 0;
        }
        orderValueUsd ++;
        $("#order-value-usd").val(orderValueUsd);
        postTradeParameters();
    });

    $("#decrease-order-value-usd").on('click', function (e) {
        var orderValueUsd = $("#order-value-usd").val();
        if(!orderValueUsd || isNaN(orderValueUsd)){
            orderValueUsd = 0;
        }
        orderValueUsd --;
        if(orderValueUsd <= 5){
            orderValueUsd = 5;
        }
        $("#order-value-usd").val(orderValueUsd);
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

    $("#threshold-usd, #order-value-usd, #add-or-sub-to-order-usd").on('change', function (e) {
        if(running === true) {
            postTradeParameters();
        }
    });
});

function postTradeParameters() {
    var thresholdUsd = $("#threshold-usd").val();
    var orderValueUsd = $("#order-value-usd").val();
    var addOrSubToOrderUsd = $("#add-or-sub-to-order-usd").val();
    $.post(Routing.generate('changeTradeParameters', {thresholdUsd:thresholdUsd, orderValueUsd:orderValueUsd, addOrSubToOrderUsd:addOrSubToOrderUsd}), function (data) {
        $("#threshold-usd").val(data.thresholdUsd);
        $("#order-value-usd").val(data.orderValueUsd)
        $("#add-or-sub-to-order-usd").val(data.addOrSubToOrderUsd)
    });
}

function stopRunning(){
    $("#start-btn").removeClass('btn-default').addClass('btn-primary');
    $("#stop-btn").removeClass('btn-danger').addClass('btn-default');
    $("#trading-since").html("");
    $("#trading-time-minutes").prop('disabled', false);
    $("#max-open-orders").prop('disabled', false);
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
    });

    $.post(Routing.generate('orderPair', {}), function (data) {
        $("#order-pair-table").html(data);
    });
}
