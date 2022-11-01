function playA() {
    $("#antrian")[0].play();
    totalwaktu = document.getElementById('antrian').duration * 1200;
    setTimeout(function() {
        $("#a")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    antrian = $('#antrianA').text();
    antrian = antrian.replace("A", "");
    if (antrian < 10) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 10) {
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 11) {
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian < 20) {
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 20) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 99) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 900;
    } else if (antrian == 100) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 109) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 110) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 111) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 119) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 120 || antrian == 130 || antrian == 140 || antrian == 150 || antrian == 160 || antrian == 170 || antrian == 180 || antrian == 190) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 199) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 200) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 209) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 210) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 211) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 219) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 220 || antrian == 230 || antrian == 240 || antrian == 250 || antrian == 260 || antrian == 270 || antrian == 280 || antrian == 290) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 299) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 300) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 309) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 310) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 311) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 319) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 320 || antrian == 330 || antrian == 340 || antrian == 350 || antrian == 360 || antrian == 370 || antrian == 380 || antrian == 390) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 399) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 400) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 409) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 410) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 411) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 419) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 420 || antrian == 430 || antrian == 440 || antrian == 450 || antrian == 460 || antrian == 470 || antrian == 480 || antrian == 490) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 499) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 500) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 509) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 510) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 511) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 519) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 520 || antrian == 530 || antrian == 540 || antrian == 550 || antrian == 560 || antrian == 570 || antrian == 580 || antrian == 590) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 599) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabela2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 600) {
        setTimeout(function() {
            $("#suarabela0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    }

    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#counter")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#suarabelloket")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#notif")[0].play()
    }, totalwaktu);
}

function playB() {
    $("#antrian")[0].play();
    totalwaktu = document.getElementById('antrian').duration * 1200;
    setTimeout(function() {
        $("#b")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    antrian = $('#antrianB').text();
    antrian = antrian.replace("B", "");
    if (antrian < 10) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 10) {
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 11) {
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian < 20) {
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 20) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 99) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 900;
    } else if (antrian == 100) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 109) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 110) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 111) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 119) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 120 || antrian == 130 || antrian == 140 || antrian == 150 || antrian == 160 || antrian == 170 || antrian == 180 || antrian == 190) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 199) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 200) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 209) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 210) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 211) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 219) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 220 || antrian == 230 || antrian == 240 || antrian == 250 || antrian == 260 || antrian == 270 || antrian == 280 || antrian == 290) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 299) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 300) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 309) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 310) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 311) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 319) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 320 || antrian == 330 || antrian == 340 || antrian == 350 || antrian == 360 || antrian == 370 || antrian == 380 || antrian == 390) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 399) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 400) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 409) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 410) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 411) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 419) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 420 || antrian == 430 || antrian == 440 || antrian == 450 || antrian == 460 || antrian == 470 || antrian == 480 || antrian == 490) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 499) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 500) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 509) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 510) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 511) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 519) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 520 || antrian == 530 || antrian == 540 || antrian == 550 || antrian == 560 || antrian == 570 || antrian == 580 || antrian == 590) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 599) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelb2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 600) {
        setTimeout(function() {
            $("#suarabelb0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    }

    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#counter")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#suarabelloket")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#notif")[0].play()
    }, totalwaktu);
}

function playC() {
    $("#antrian")[0].play();
    totalwaktu = document.getElementById('antrian').duration * 1200;
    setTimeout(function() {
        $("#c")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    antrian = $('#antrianC').text();
    antrian = antrian.replace("C", "");
    if (antrian < 10) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 10) {
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 11) {
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian < 20) {
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 20) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 99) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 900;
    } else if (antrian == 100) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 109) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 110) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 111) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 119) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 120 || antrian == 130 || antrian == 140 || antrian == 150 || antrian == 160 || antrian == 170 || antrian == 180 || antrian == 190) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 199) {
        setTimeout(function() {
            $("#seratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 200) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 209) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 210) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 211) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 219) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 220 || antrian == 230 || antrian == 240 || antrian == 250 || antrian == 260 || antrian == 270 || antrian == 280 || antrian == 290) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 299) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 300) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 309) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 310) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 311) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 319) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 320 || antrian == 330 || antrian == 340 || antrian == 350 || antrian == 360 || antrian == 370 || antrian == 380 || antrian == 390) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 399) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 400) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 409) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 410) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 411) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 419) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 420 || antrian == 430 || antrian == 440 || antrian == 450 || antrian == 460 || antrian == 470 || antrian == 480 || antrian == 490) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 499) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 500) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 509) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000
    } else if (antrian == 510) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sepuluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 511) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#sebelas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 519) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#belas")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 520 || antrian == 530 || antrian == 540 || antrian == 550 || antrian == 560 || antrian == 570 || antrian == 580 || antrian == 590) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
      	setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian <= 599) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc1")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#puluh")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#suarabelc2")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    } else if (antrian == 600) {
        setTimeout(function() {
            $("#suarabelc0")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
        setTimeout(function() {
            $("#ratus")[0].play()
        }, totalwaktu);
        totalwaktu = totalwaktu + 1000;
    }

    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#counter")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#suarabelloket")[0].play()
    }, totalwaktu);
    totalwaktu = totalwaktu + 1000;
    setTimeout(function() {
        $("#notif")[0].play()
    }, totalwaktu);
}