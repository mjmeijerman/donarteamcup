function aantal_plekken() {
    $.ajax({
        type: 'get',
        url: Routing.generate('aantalVrijePlekkenAjaxCall'),
        success: function (data) {
            document.getElementById("aantal_vrije_plekken").innerHTML = data;
        }
    });
}

// FUNCTIE: IF VERENIGING SELECTED AND CHECKBOX CHECKED -> FOUTMELDING

function vereniging_bestaat_niet() {
    aantal_plekken();
    var check = document.getElementById("verenigingnaam").value;
    if (check != "") {
        document.getElementById("error_container").innerHTML = '<div id="error"><span id="vereniging_error"><b>FOUTMELDING:</b> Je hebt wel een' +
            ' vereniging geselecteerd. Als je vereniging er inderdaad niet tussen staat, deselecteer dan eerst de vereniging!</span></div>';
        document.getElementById("verenigingstaaternietbijikbenzozielig").checked = false;
    }
    else {
        if (document.getElementById("general_contact_error")) {
            document.getElementById("error_container").innerHTML = '';
        }
        document.getElementById("inschrijven_contactpersoon").style.display = 'none';
        var check1 = document.getElementById("verenigingstaaternietbijikbenzozielig").checked;
        var x = document.getElementById("inschrijven_nieuwe_vereniging").innerHTML;
        if (check1 == true) {
            document.getElementById('inschrijven_nieuwe_vereniging').style.display = '';
            document.getElementById('inschrijven_nieuwe_vereniging').innerHTML = '<div class="fadein">' + x + '</div>';
        }
        else if (check1 == false) {
            document.getElementById('inschrijven_nieuwe_vereniging').style.display = 'none';
            document.getElementById('inschrijven_vereniging_header').className = '';
        }
    }
}

// FUNCTIES VOOR VERENIGING FORMULIER

function check_vereniging() {
    aantal_plekken();
    var theForm = document.forms["vereniging"];
    var check1 = document.getElementById('verenigingsnaam').value;
    var check2 = document.getElementById('verenigingsplaats').value;
    var check3 = theForm.elements["verenigingnaam"].value;
    if ((check3 === "" || check3 === null) && check1 !== "" && check2 !== "" && check1 !== null && check2 !== null) {
        if (validate_vereniging_fields()) {
            show_contactpersoon();
            document.getElementById('inschrijven_vereniging_header').className = 'success';
            if (document.getElementById("general_vereniging_error")) {
                document.getElementById("error_container").innerHTML = '';
            }
        } else {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="general_vereniging_error"><b>FOUTMELDING:</b> Niet alle' +
                ' velden zijn correct ingevoerd!</span></div>';
            document.getElementById('inschrijven_contactpersoon').style.display = 'none';
            document.getElementById('inschrijven_reserveren').style.display = 'none';
            document.getElementById('inschrijven_vereniging_header').className = '';
            document.getElementById('inschrijven_contactpersoon_header').className = '';
            document.getElementById('verenigingnaam').className = 'select';
        }
    } else {
        if (check3 !== "") {
            document.getElementById("verenigingstaaternietbijikbenzozielig").checked = false;
            show_contactpersoon();
            document.getElementById('verenigingstaaternietbijikbenzozielig').checked = false;
            document.getElementById('inschrijven_nieuwe_vereniging').style.display = 'none';
            document.getElementById('inschrijven_vereniging_header').className = 'success';
            document.getElementById('verenigingnaam').className = 'selectIngevuld';
        }
        else {
            document.getElementById('inschrijven_contactpersoon').style.display = 'none';
            document.getElementById('inschrijven_reserveren').style.display = 'none';
            document.getElementById('inschrijven_vereniging_header').className = '';
            document.getElementById('inschrijven_contactpersoon_header').className = '';
            document.getElementById('verenigingnaam').className = 'select';
        }
    }
}

function show_contactpersoon() {
    aantal_plekken();
    validate_contact_fields();
    var z = document.getElementById('inschrijven_contactpersoon').style.display;
    if (z !== '') {
        var x = document.getElementById('inschrijven_contactpersoon').innerHTML;
        document.getElementById('inschrijven_contactpersoon').style.display = '';
        document.getElementById('inschrijven_contactpersoon').innerHTML = '<div class="appear">' + x + '</div>';
    }
}

function update_vereningsnaam() {
    aantal_plekken();
    var a = (document.getElementById('verenigingnaam').value.split("_"))[1];
    var b = document.getElementById("verenigingsnaam");
    var c = document.getElementById("verenigingsplaats");
    var d = b.value + ', ' + c.value;

    if (a) {
        var vereniging = a;
    }
    else {
        var vereniging = d.toUpperCase();
    }
    document.getElementById('inschrijven_verenigingsnaam').innerHTML = vereniging;
}

// FUNCTIES VOOR CONTACTPERSOON FORMULIER

function check_contactpersoon() {
    aantal_plekken();
    var theForm = document.forms["inschrijven_contactpersoon"];
    var voornaam = theForm.elements["voornaam"];
    var achternaam = theForm.elements["achternaam"];
    var email = theForm.elements["email"];
    var telefoonnummer = theForm.elements["telefoonnummer"];
    var username = theForm.elements["username"];
    var wachtwoord = theForm.elements["wachtwoord"];
    var wachtwoord2 = theForm.elements["wachtwoord2"];
    if (voornaam.value !== "" && achternaam.value !== "" && telefoonnummer.value, email.value !== "" && username.value !== "" && wachtwoord.value !== "" && wachtwoord2.value !== "" && wachtwoord.value == wachtwoord2.value) {
        if (validate_contact_fields()) {
            show_reserveren();
            document.getElementById('inschrijven_contactpersoon_header').className = 'success';
            if (document.getElementById("general_contact_error")) {
                document.getElementById("error_container").innerHTML = '';
            }
        } else {
            document.getElementById("error_container").innerHTML = '<div id="error"><span id="general_contact_error"><b>FOUTMELDING:</b> Niet alle' +
                ' velden zijn correct ingevoerd!</span></div>';
            document.getElementById('inschrijven_reserveren').style.display = 'none';
            document.getElementById('inschrijven_contactpersoon_header').className = '';
        }
    } else {
        document.getElementById('inschrijven_reserveren').style.display = 'none';
        document.getElementById('inschrijven_contactpersoon_header').className = '';
    }
}

function validate_vereniging_fields() {
    var vereniging_naam = validate_vereniging_naam(false);
    var vereniging_plaats = validate_vereniging_plaats(false);
    if (vereniging_naam && vereniging_plaats) {
        return true;
    } else {
        return false;
    }
}

function validate_contact_fields() {
    var email = validate_email(false);
    var voornaam = validate_voornaam(false);
    var achternaam = validate_achternaam(false);
    var telefoonnummer = validate_telefoonnummer(false);
    if (document.getElementById("username").className == 'succesIngevuld') {
        var username = true;
    } else {
        var username = false;
    }
    var wachtwoord = validate_wachtwoord(false);
    var wachtwoord2 = validate_wachtwoord2(false);
    if (email && voornaam && achternaam && telefoonnummer && username && wachtwoord && wachtwoord2) {
        return true;
    } else {
        return false;
    }
}

function validate_email(show_error_messages) {
    var validated = false;
    var email = document.getElementById("email");
    var re = /^((?:[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-zA_Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-zA-Z0-9-]*[a-zA-Z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\]))$/;
    if (!email.value) {
        email.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="email_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen emailadres ingevoerd!</span></div>';
        }
    } else if (re.test(email.value)) {
        email.className = 'succesIngevuld';
        validated = true;
        if (document.getElementById("email_error")) {
            document.getElementById("error_container").innerHTML = '';
        }
        if (show_error_messages) {
            check_contactpersoon()
        }
    } else {
        email.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span id="email_error"><b>FOUTMELDING:</b> Je hebt geen' +
                ' geldig emailadres ingevoerd!</span></div>';
        }
    }
    return validated;
}

function validate_voornaam(show_error_messages) {
    var validated = false;
    var voornaam = document.getElementById("voornaam");
    if (!voornaam.value) {
        voornaam.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="voornaam_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen voornaam ingevoerd!</span></div>';
        }
    } else if (voornaam.value.length > 1) {
        voornaam.className = 'succesIngevuld';
        validated = true;
        if (document.getElementById("voornaam_error")) {
            document.getElementById("error_container").innerHTML = '';
        }
        if (show_error_messages) {
            check_contactpersoon()
        }
    } else {
        voornaam.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span id="voornaam_error"><b>FOUTMELDING:</b> Je hebt geen' +
                ' geldige voornaam ingevoerd!</span></div>';
        }
    }
    return validated;
}

function validate_achternaam(show_error_messages) {
    var validated = false;
    var achternaam = document.getElementById("achternaam");
    if (!achternaam.value) {
        achternaam.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="achternaam_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen achternaam ingevoerd!</span></div>';
        }
    } else if (achternaam.value.length > 1) {
        achternaam.className = 'succesIngevuld';
        validated = true;
        if (document.getElementById("achternaam_error")) {
            document.getElementById("error_container").innerHTML = '';
        }
        if (show_error_messages) {
            check_contactpersoon()
        }
    } else {
        achternaam.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span id="achternaam_error"><b>FOUTMELDING:</b> Je hebt geen' +
                ' geldige achternaam ingevoerd!</span></div>';
        }
    }
    return validated;
}

function validate_telefoonnummer(show_error_messages) {

    var validated = false;
    var telefoonnummer = document.getElementById("telefoonnummer");
    if (!telefoonnummer.value) {
        telefoonnummer.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="telefoonnummer_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen telefoonnummer ingevoerd!</span></div>';
        }
    } else if (telefoonnummer.value.length === 10) {
        var re = /^([0-9]+)$/;
        if (re.test(telefoonnummer.value)) {
            telefoonnummer.className = 'succesIngevuld';
            validated = true;
            if (document.getElementById("telefoonnummer_error")) {
                document.getElementById("error_container").innerHTML = '';
            }
            if (show_error_messages) {
                check_contactpersoon()
            }
        } else {
            telefoonnummer.className = 'error';
            if (show_error_messages) {
                document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                    ' id="telefoonnummer_error"><b>FOUTMELDING:</b> Het telefoonnummer mag alleen uit' +
                    ' cijfers bestaan!</span></div>';
            }
        }

    } else {
        telefoonnummer.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="telefoonnummer_error"><b>FOUTMELDING:</b> Het telefoonnummer moet uit precies 10' +
                ' cijfers bestaan en mag geen andere karakters bevatten!</span></div>';
        }
    }
    return validated;
}

function validate_username(show_error_messages) {
    var validated = false;
    var username = document.getElementById("username");
    if (!username.value) {
        username.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="username_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen inlognaam ingevoerd!</span></div>';
        }
    } else if (username.value.length > 1) {
        $.ajax({
            type: 'get',
            url: Routing.generate('checkUsernameAvailabilityAjaxCall', {username: username.value}),
            success: function (data) {
                if (data == 'true') {
                    username.className = 'succesIngevuld';
                    validated = true;
                    if (document.getElementById("username_error")) {
                        document.getElementById("error_container").innerHTML = '';
                    }
                    if (show_error_messages) {
                        check_contactpersoon()
                    }
                } else {
                    username.className = 'error';
                    if (show_error_messages) {
                        document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                            ' id="username_error"><b>FOUTMELDING:</b> Deze username is al in gebruik!</span></div>';
                    }
                }
            }
        });
    } else {
        username.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span id="username_error"><b>FOUTMELDING:</b> Je hebt geen' +
                ' geldige inlognaam ingevoerd!</span></div>';
        }
    }
    return validated;
}

function validate_vereniging_naam(show_error_messages) {
    var validated = false;
    var verenigingsnaam = document.getElementById("verenigingsnaam");
    if (!verenigingsnaam.value) {
        verenigingsnaam.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="verenigingsnaam_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen verenigingsnaam ingevoerd!</span></div>';
        }
    } else if (verenigingsnaam.value.length > 1) {
        verenigingsnaam.className = 'succesIngevuld';
        validated = true;
        if (document.getElementById("verenigingsnaam_error")) {
            document.getElementById("error_container").innerHTML = '';
        }
        if (show_error_messages) {
            check_vereniging()
        }
    } else {
        verenigingsnaam.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span id="verenigingsnaam_error"><b>FOUTMELDING:</b> Je hebt geen' +
                ' geldige verenigingsnaam ingevoerd!</span></div>';
        }
    }
    return validated;
}

function validate_vereniging_plaats(show_error_messages) {
    var validated = false;
    var plaats = document.getElementById("verenigingsplaats");
    if (!plaats.value) {
        plaats.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="plaats_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen plaats ingevoerd!</span></div>';
        }
    } else if (plaats.value.length > 1) {
        plaats.className = 'succesIngevuld';
        validated = true;
        if (document.getElementById("plaats_error")) {
            document.getElementById("error_container").innerHTML = '';
        }
        if (show_error_messages) {
            check_vereniging()
        }
    } else {
        plaats.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span id="plaats_error"><b>FOUTMELDING:</b> Je hebt geen' +
                ' geldige plaats ingevoerd!</span></div>';
        }
    }
    return validated;
}

function validate_wachtwoord(show_error_messages) {
    var validated = false;
    var wachtwoord = document.getElementById("wachtwoord");
    var wachtwoord2 = document.getElementById("wachtwoord2");
    if (!wachtwoord.value) {
        wachtwoord.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="wachtwoord_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen wachtwoord ingevoerd!</span></div>';
        }
    } else if (wachtwoord.value.length > 5) {
        if (wachtwoord2.value.length != 0 && wachtwoord.value != wachtwoord2.value) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="wachtwoord_error"><b>FOUTMELDING:</b> De ingevoerde wachtwoorden zijn niet gelijk!</span></div>';
            wachtwoord.className = 'error';
            wachtwoord2.className = 'error';
        } else if (wachtwoord2.value.length == 0) {
            wachtwoord.className = 'succesIngevuld';
            validated = true;
            if (document.getElementById("wachtwoord_error")) {
                document.getElementById("error_container").innerHTML = '';
            }
            if (show_error_messages) {
                check_contactpersoon()
            }
        } else {
            wachtwoord.className = 'succesIngevuld';
            wachtwoord2.className = 'succesIngevuld';
            validated = true;
            if (document.getElementById("wachtwoord_error")) {
                document.getElementById("error_container").innerHTML = '';
            }
            if (show_error_messages) {
                check_contactpersoon()
            }
        }
    } else {
        wachtwoord.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="achternaam_error"><b>FOUTMELDING:</b> Dit wachtwoord is te kort!</span></div>';
        }
    }
    return validated;
}

function validate_wachtwoord2(show_error_messages) {
    var validated = false;
    var wachtwoord = document.getElementById("wachtwoord2");
    var wachtwoord2 = document.getElementById("wachtwoord");
    if (!wachtwoord.value) {
        wachtwoord.className = 'text';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="wachtwoord_error"><b>FOUTMELDING:</b> Je hebt' +
                ' geen wachtwoord ingevoerd!</span></div>';
        }
    } else if (wachtwoord.value.length > 5) {

        if (wachtwoord2.value.length != 0 && wachtwoord.value != wachtwoord2.value) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="wachtwoord_error"><b>FOUTMELDING:</b> De ingevoerde wachtwoorden zijn niet gelijk!</span></div>';
            wachtwoord.className = 'error';
            wachtwoord2.className = 'error';
        } else {
            wachtwoord.className = 'succesIngevuld';
            wachtwoord2.className = 'succesIngevuld';
            validated = true;
            if (document.getElementById("wachtwoord_error")) {
                document.getElementById("error_container").innerHTML = '';
            }
            if (show_error_messages) {
                check_contactpersoon()
            }
        }
    } else {
        wachtwoord.className = 'error';
        if (show_error_messages) {
            document.getElementById("error_container").innerHTML = '<div id="error"><span' +
                ' id="achternaam_error"><b>FOUTMELDING:</b> Dit wachtwoord is te kort!</span></div>';
        }
    }
    return validated;
}

function show_reserveren() {
    aantal_plekken();
    var z = document.getElementById('inschrijven_reserveren').style.display;
    if (z !== '') {
        var x = document.getElementById('inschrijven_reserveren').innerHTML;
        document.getElementById('inschrijven_reserveren').style.display = '';
        document.getElementById('inschrijven_reserveren').innerHTML = '<div class="appear">' + x + '</div>';
    }
}

// FUNCTIES VOOR HET RESERVEREN

function update_reserveer_display() {
    aantal_plekken();
    if (document.getElementById('reserveer_aantal_invoer').value < 1) {
        document.getElementById("error_container").innerHTML = '<div id="error"><span' +
            ' id="aantal_error"><b>FOUTMELDING:</b> Het aantal turnsters moet groter zijn dan 0!</span></div>';
        document.getElementById('reserveer_aantal_invoer').className = 'error';
        document.getElementById('aantal_plekken_header').className = '';
        document.getElementById('reserveer_aantal').innerHTML = '0 plekken reserveren!';
    } else {
        var z = document.getElementById('reserveer_aantal_invoer').value;
        if (z !== '') {
            if (z == 1) {
                document.getElementById('reserveer_aantal').innerHTML = '1 plek reserveren!';
            }
            else {
                document.getElementById('reserveer_aantal').innerHTML = z + ' plekken reserveren!';
            }
            document.getElementById("error_container").innerHTML = '';
            document.getElementById('reserveer_aantal_invoer').className = 'numberIngevuld';
            document.getElementById('aantal_plekken_header').className = 'success';
        }
        else {
            document.getElementById('reserveer_aantal').innerHTML = '0 plekken reserveren!';
            document.getElementById('reserveer_aantal_invoer').className = 'number';
            document.getElementById('aantal_plekken_header').className = '';
        }
    }
}

function post_turnsters() {
    document.forms["turnsters"].submit();
}

function post_gegevens() {
    if (document.getElementById('reserveer_aantal_invoer').value < 1) {
        document.getElementById("error_container").innerHTML = '<div id="error"><span' +
            ' id="aantal_error"><b>FOUTMELDING:</b> Het aantal turnsters moet groter zijn dan 0!</span></div>';
        document.getElementById('reserveer_aantal_invoer').className = 'error';
        return;
    }
    document.getElementById('reserveer_button').style.pointerEvents = 'none';
    document.getElementById('post_verenigingsnaam').value = document.getElementById('verenigingsnaam').value;
    document.getElementById('post_verenigingsplaats').value = document.getElementById('verenigingsplaats').value;
    document.getElementById('post_verenigingsid').value = (document.getElementById('verenigingnaam').value.split("_"))[0];
    document.getElementById('post_voornaam').value = document.getElementById('voornaam').value;
    document.getElementById('post_achternaam').value = document.getElementById('achternaam').value;
    document.getElementById('post_email').value = document.getElementById('email').value;
    document.getElementById('post_telefoonnummer').value = document.getElementById('telefoonnummer').value;
    document.getElementById('post_username').value = document.getElementById('username').value;
    document.getElementById('post_wachtwoord').value = document.getElementById('wachtwoord').value;
    document.getElementById('post_wachtwoord2').value = document.getElementById('wachtwoord2').value;
    document.getElementById('post_aantalturnsters').value = document.getElementById('reserveer_aantal_invoer').value;
    document.forms["post_form"].submit();
}

function get_niveaus(key, turnsterNiveau) {
    if (!key) {
        var geboorteJaar = document.getElementById('geboorteJaar').value;
        var niveau = document.getElementById('mogelijke_niveaus');
    } else {
        var geboorteJaar = document.getElementById('geboorteJaar_' + key).value;
        var niveau = document.getElementById('mogelijke_niveaus_' + key);
    }
    $.ajax({
        type: 'get',
        url: Routing.generate('getAvailableNiveausAjaxCall', {geboorteJaar: geboorteJaar}),
        success: function (data) {
            niveau.innerHTML = '<option value="" selected>Niveau</option>';
            for (var field in data) {
                var selected = '';
                if (turnsterNiveau == data[field]) {
                    selected = 'selected';
                }
                niveau.innerHTML += '<option value="' + data[field] + '" ' + selected + '>' + data[field] + '</option>';
            }
        }
    });
}

function afsluiten()
{
    document.getElementById('remove_session').value = true;
    document.forms["turnsters"].submit();
}
