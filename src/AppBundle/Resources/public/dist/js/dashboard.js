setInterval(function(){
    if(running === true) {
        $.post(Routing.generate('balance', {}), function (data) {
            $("#balance-table").html(data);
        });
    }

    $.post(Routing.generate('ticker', {}), function (data) {
        $("#ticker-table").html(data);
    });
}, 3000);

$("#start-btn").on( 'click', function (e) {
    e.preventDefault();
    var form = document.getElementById("trading-form");
    if(form.checkValidity()){
        var thresholdUsd = $("#threshold-usd").val();
        var orderValueUsd = $("#order-value-usd").val();
        var tradingTimeMinutes = $("#trading-time-minutes").val();

        $.post( Routing.generate('startTrading', {thresholdUsd: thresholdUsd, orderValueUsd:orderValueUsd, tradingTimeMinutes:tradingTimeMinutes}), function( data ) {
            if(data.running === true) {
                $("#start-btn").removeClass('btn-primary').addClass('btn-default');
                $("#stop-btn").removeClass('btn-default').addClass('btn-danger');
                $("#trading-since").html("Trading start time: " + data.startDate);
                $("#threshold-usd").prop('disabled', true);
                $("#order-value-usd").prop('disabled', true);
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
            $("#start-btn").removeClass('btn-default').addClass('btn-primary');
            $("#stop-btn").removeClass('btn-danger').addClass('btn-default');
            $("#trading-since").html("");
            $("#threshold-usd").prop('disabled', false);
            $("#order-value-usd").prop('disabled', false);
            $("#trading-time-minutes").prop('disabled', false);

            running = false;
        }
    });
});
