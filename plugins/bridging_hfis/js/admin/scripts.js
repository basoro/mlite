function ubahhfis() {
    var baseURL = mlite.url + '/' + mlite.admin;
    var url = baseURL + '/bridging_hfis/updatebridgehfis?t=' + mlite.token;
    var kd_dokter = document.getElementById("kd_dokter").value;
    var kd_poli = document.getElementById("kd_poli").value;

    var senin = document.getElementById("senin").checked;
    var selasa = document.getElementById("selasa").checked;
    var rabu = document.getElementById("rabu").checked;
    var kamis = document.getElementById("kamis").checked;
    var jumat = document.getElementById("jumat").checked;
    var sabtu = document.getElementById("sabtu").checked;
    var seninsore = document.getElementById("seninsore").checked;
    var selasasore = document.getElementById("selasasore").checked;
    var rabusore = document.getElementById("rabusore").checked;
    var kamissore = document.getElementById("kamissore").checked;
    var jumatsore = document.getElementById("jumatsore").checked;
    var sabtusore = document.getElementById("sabtusore").checked;

    var senin_value = document.getElementById("senin").value;
    var selasa_value = document.getElementById("selasa").value;
    var rabu_value = document.getElementById("rabu").value;
    var kamis_value = document.getElementById("kamis").value;
    var jumat_value = document.getElementById("jumat").value;
    var sabtu_value = document.getElementById("sabtu").value;
    var seninsore_value = document.getElementById("seninsore").value;
    var selasasore_value = document.getElementById("selasasore").value;
    var rabusore_value = document.getElementById("rabusore").value;
    var kamissore_value = document.getElementById("kamissore").value;
    var jumatsore_value = document.getElementById("jumatsore").value;
    var sabtusore_value = document.getElementById("sabtusore").value;

    var kuotasenin_value = document.getElementById("kuotasenin").value;
    var kuotaselasa_value = document.getElementById("kuotaselasa").value;
    var kuotarabu_value = document.getElementById("kuotarabu").value;
    var kuotakamis_value = document.getElementById("kuotakamis").value;
    var kuotajumat_value = document.getElementById("kuotajumat").value;
    var kuotasabtu_value = document.getElementById("kuotasabtu").value;
    var kuotaseninsore_value = document.getElementById("kuotaseninsore").value;
    var kuotaselasasore_value = document.getElementById("kuotaselasasore").value;
    var kuotarabusore_value = document.getElementById("kuotarabusore").value;
    var kuotakamissore_value = document.getElementById("kuotakamissore").value;
    var kuotajumatsore_value = document.getElementById("kuotajumatsore").value;
    var kuotasabtusore_value = document.getElementById("kuotasabtusore").value;

    var json = { "kd_dokter": kd_dokter, "kd_poli": kd_poli };
    var jadwal = [];
    json.jadwal = jadwal;
    if (senin == true) {
        var dari = document.getElementById("dari" + senin_value).value;
        var sampai = document.getElementById("sampai" + senin_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": senin_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotasenin_value
        }
        jadwal.push(list);
        // }
    }
    if (selasa == true) {
        var dari = document.getElementById("dari" + selasa_value).value;
        var sampai = document.getElementById("sampai" + selasa_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": selasa_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotaselasa_value
        }
        jadwal.push(list);
        // }
    }
    if (rabu == true) {
        var dari = document.getElementById("dari" + rabu_value).value;
        var sampai = document.getElementById("sampai" + rabu_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": rabu_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotarabu_value
        }
        jadwal.push(list);
        // }
    }
    if (kamis == true) {
        var dari = document.getElementById("dari" + kamis_value).value;
        var sampai = document.getElementById("sampai" + kamis_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": kamis_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotakamis_value
        }
        jadwal.push(list);
        // }
    }
    if (jumat == true) {
        var dari = document.getElementById("dari" + jumat_value).value;
        var sampai = document.getElementById("sampai" + jumat_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": jumat_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotajumat_value
        }
        jadwal.push(list);
        // }
    }
    if (sabtu == true) {
        var dari = document.getElementById("dari" + sabtu_value).value;
        var sampai = document.getElementById("sampai" + sabtu_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": sabtu_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotasabtu_value
        }
        jadwal.push(list);
        // }
    }
    if (seninsore == true) {
        var dari = document.getElementById("darisore" + seninsore_value).value;
        var sampai = document.getElementById("sampaisore" + seninsore_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": seninsore_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotaseninsore_value
        }
        jadwal.push(list);
        // }
    }
    if (selasasore == true) {
        var dari = document.getElementById("darisore" + selasasore_value).value;
        var sampai = document.getElementById("sampaisore" + selasasore_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": selasasore_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotaselasasore_value
        }
        jadwal.push(list);
        // }
    }
    if (rabusore == true) {
        var dari = document.getElementById("darisore" + rabusore_value).value;
        var sampai = document.getElementById("sampaisore" + rabusore_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": rabusore_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotarabusore_value
        }
        jadwal.push(list);
        // }
    }
    if (kamissore == true) {
        var dari = document.getElementById("darisore" + kamissore_value).value;
        var sampai = document.getElementById("sampaisore" + kamissore_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": kamissore_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotakamissore_value
        }
        jadwal.push(list);
        // }
    }
    if (jumatsore == true) {
        var dari = document.getElementById("darisore" + jumatsore_value).value;
        var sampai = document.getElementById("sampaisore" + jumatsore_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": jumatsore_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotajumatsore_value
        }
        jadwal.push(list);
        // }
    }
    if (sabtusore == true) {
        var dari = document.getElementById("darisore" + sabtusore_value).value;
        var sampai = document.getElementById("sampaisore" + sabtusore_value).value;
        // if (dari != '' || sampai != '') {
        var list = {
            "hari": sabtusore_value,
            "buka": dari,
            "tutup": sampai,
            "kuota": kuotasabtusore_value
        }
        jadwal.push(list);
        // }
    }
    const http = new XMLHttpRequest()
    http.open('POST', url);

    http.setRequestHeader('Content-type', 'application/json');
    http.send(JSON.stringify(json))

    http.onreadystatechange = function () {
        if (http.readyState == 4 && http.status == 200) {
            alert(http.responseText);
        }
    }
}