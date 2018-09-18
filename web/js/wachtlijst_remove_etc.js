function remove_turnster(id, naam, vereniging) {
    if (confirm("Weet je zeker dat je " + naam + " van " + vereniging + " wilt verwijderen?")) {
        var row = document.getElementById('turnster_row_' + id);
        var aantal_deelnemers = document.getElementById('turnsters_aantal');
        aantal_deelnemers.innerHTML = (parseInt(aantal_deelnemers.innerHTML) - 1);
        row.innerHTML = '';
        $.ajax({
            type: 'get',
            url: Routing.generate('removeOrganisatieTurnsterAjaxCall', {id: id})
        });
    }
}

function remove_turnster_wachtlijst(id, naam, vereniging) {
    if (confirm("Weet je zeker dat je " + naam + " van " + vereniging + " wilt verwijderen?")) {
        var row = document.getElementById('turnster_row_' + id);
        var aantal_deelnemers = document.getElementById('wachtlijst_aantal');
        aantal_deelnemers.innerHTML = (parseInt(aantal_deelnemers.innerHTML) - 1);
        row.innerHTML = '';
        $.ajax({
            type: 'get',
            url: Routing.generate('removeOrganisatieTurnsterAjaxCall', {id: id})
        });
    }
}

function remove_turnster_afgemeld(id, naam, vereniging) {
    if (confirm("Weet je zeker dat je " + naam + " van " + vereniging + " wilt verwijderen?")) {
        var row = document.getElementById('turnster_row_' + id);
        var aantal_deelnemers = document.getElementById('afgemeld_aantal');
        aantal_deelnemers.innerHTML = (parseInt(aantal_deelnemers.innerHTML) - 1);
        row.innerHTML = '';
        $.ajax({
            type: 'get',
            url: Routing.generate('removeOrganisatieTurnsterAjaxCall', {id: id})
        });
    }
}

function remove_niveau(id, categorie, niveau, page) {
	if (confirm("Weet je zeker dat je " + categorie + ": " + niveau + " wilt verwijderen?")) {
		var row = document.getElementById('niveau_row_' + id);
		row.innerHTML = '';
		$.ajax({
			type: 'get',
			url: Routing.generate('niveauVerwijderenAjaxCall', {id: id, page: page})
		});
	}
}

function remove_jurylid(id, naam, vereniging) {
    if (confirm("Weet je zeker dat je " + naam + " van " + vereniging + " wilt verwijderen?")) {
        var row = document.getElementById('jurylid_row_' + id);
        var aantal_jury = document.getElementById('juryleden_aantal');
        aantal_jury.innerHTML = (parseInt(aantal_jury.innerHTML) - 1);
        row.innerHTML = '';
        $.ajax({
            type: 'get',
            url: Routing.generate('removeOrganisatieJuryAjaxCall', {id: id})
        });
    }
}

function naar_wachtlijst(id)
{
	var table = document.getElementById("wachtlijst_table");
	var new_row = table.insertRow(-1);
	new_row.id = 'turnster_row_' + id;
	var old_row = document.getElementById('turnster_row_' + id);
	var aantal_deelnemers = document.getElementById('turnsters_aantal');
	aantal_deelnemers.innerHTML = (parseInt(aantal_deelnemers.innerHTML) - 1);
	var aantal_deelnemers_wachtlijst = document.getElementById('wachtlijst_aantal');
	aantal_deelnemers_wachtlijst.innerHTML = (parseInt(aantal_deelnemers_wachtlijst.innerHTML) + 1);
	old_row_HTML = old_row.innerHTML;
	old_row_HTML_UpArrow = old_row_HTML.replace("images/down.png", "images/up.png");
	old_row_correct = old_row_HTML_UpArrow.replace("naar_wachtlijst", "van_wachtlijst");
	new_row.innerHTML = old_row_correct;
	old_row.innerHTML = '';
	$.ajax({
		type: 'get',
		url: Routing.generate('moveTurnsterToWachtlijst', {id: id})
	});
}

function van_wachtlijst(id)
{
	var table = document.getElementById("turnster_table");
	var new_row = table.insertRow(-1);
	var old_row = document.getElementById('turnster_row_' + id);
	new_row.id = 'turnster_row_' + id;
	var aantal_deelnemers = document.getElementById('turnsters_aantal');
	aantal_deelnemers.innerHTML = (parseInt(aantal_deelnemers.innerHTML) + 1);
	var aantal_deelnemers_wachtlijst = document.getElementById('wachtlijst_aantal');
	aantal_deelnemers_wachtlijst.innerHTML = (parseInt(aantal_deelnemers_wachtlijst.innerHTML) - 1);
	old_row_HTML = old_row.innerHTML;
	old_row_HTML_DownArrow = old_row_HTML.replace("images/up.png", "images/down.png");
	old_row_correct = old_row_HTML_DownArrow.replace("van_wachtlijst", "naar_wachtlijst");
	new_row.innerHTML = old_row_correct;
	old_row.innerHTML = '';
	$.ajax({
		type: 'get',
		url: Routing.generate('moveTurnsterFromWachtlijst', {id: id})
	});
}

function remove_jurylid_niet(id, naam, vereniging) {
	if (confirm("Weet je zeker dat je " + naam + " van " + vereniging + " wilt verwijderen?")) {
		var row = document.getElementById('jurylid_row_' + id);
		var aantal_jury = document.getElementById('juryleden_aantal_niet');
		aantal_jury.innerHTML = (parseInt(aantal_jury.innerHTML) - 1);
		row.innerHTML = '';
		$.ajax({
			type: 'get',
			url: Routing.generate('removeOrganisatieJuryAjaxCall', {id: id})
		});
	}
}