setInterval(function(){
    if(running === true) {
        $.post(Routing.generate('isRunning', {}), function (data) {
           if(data === 'ko'){
               running = false;
               stopRunning();
           }
        });

        $.post(Routing.generate('balance', {}), function (data) {
            $("#balance-table").html(data);
        });
    }

    $.post(Routing.generate('ticker', {}), function (data) {
        $("#ticker-table").html(data);
    });

    $.post(Routing.generate('difference', {}), function (data) {
        $("#difference-table").html(data);
    });
}, 8 * 1000);

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
                $("#threshold-usd").prop('disabled', true);
                $("#order-value-usd").prop('disabled', true);
                $("#add-or-sub-to-order-usd").prop('disabled', true);
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

function stopRunning(){
    $("#start-btn").removeClass('btn-default').addClass('btn-primary');
    $("#stop-btn").removeClass('btn-danger').addClass('btn-default');
    $("#trading-since").html("");
    $("#threshold-usd").prop('disabled', false);
    $("#order-value-usd").prop('disabled', false);
    $("#add-or-sub-to-order-usd").prop('disabled', false);
    $("#trading-time-minutes").prop('disabled', false);
    $("#max-open-orders").prop('disabled', false);
}
