function onChange(data, fieldName, route) {
    if(!data) {
        data = 'null';
    }
    $.ajax({
        type: 'get',
        url: Routing.generate(route, {fieldName: fieldName, data: data}),
        success: function (data) {
            if (data.data) {
                $('#' + fieldName).text(data.data);
            } else {
                $('#' + fieldName).text('Klik om te wijzigen');
            }
            var melding;
            if (data.error) {
                melding = '<div id="error">' + data.error + '</div>';
                $('#error_container').html(melding);
            } else {
                melding = '<div id="error_success">De gegevens zijn succesvol opgeslagen</div>';
                $('#error_success_container').html(melding);
            }
        }
    });
}

function onClick(data, fieldName, type) {
    if ($('#txt_' + fieldName).length) return;
    $('#' + fieldName).html('');
    $('<input> </input>')
        .attr({
            'type': type,
            'name': 'fname',
            'id': 'txt_' + fieldName,
            'class': 'txt_edit',
            'size': '30',
            'value': data
        })
        .appendTo('#' + fieldName);
    $('#txt_' + fieldName).focus();
    var tmpStr = $('#txt_' + fieldName).val();
    $('#txt_' + fieldName).val('');
    if (tmpStr != 'Klik om te wijzigen') {
        $('#txt_' + fieldName).val(tmpStr);
    }
    $('#txt_' + fieldName).focus();
}

function onClickJurydag(data, fieldName) {
    console.log('hoi' + data + fieldName);
}
