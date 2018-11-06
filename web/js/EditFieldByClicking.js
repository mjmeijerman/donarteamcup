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

function onChangeTeamName(id, newName) {
    if(!newName) {
        newName = 'null';
    }
    $.ajax({
        type: 'get',
        url: Routing.generate('editTeamNaam', {id: id, newName: newName}),
        success: function (data) {
            if (data.data) {
                $('#team_naam_' + id).text(data.data);
            } else {
                $('#team_naam_' + id).text('Klik om te wijzigen');
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

function onClickTeamName(data, id, type) {
    if ($('#txt_' + id).length) return;
    $('#' + id).html('');
    $('<input> </input>')
        .attr({
            'type': type,
            'name': 'fname',
            'id': 'txt_' + id,
            'class': 'txt_edit_teamname',
            'size': '30',
            'value': data
        })
        .appendTo('#' + id);
    $('#txt_' + id).focus();
    var tmpStr = $('#txt_' + id).val();
    $('#txt_' + id).val('');
    if (tmpStr != 'Klik om te wijzigen') {
        $('#txt_' + id).val(tmpStr);
    }
    $('#txt_' + id).focus();
}

function onClickJurydag(data, fieldName) {
    console.log('hoi' + data + fieldName);
}
