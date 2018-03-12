$(document).ready(function() {

    // DECLARATION DU CALENDAR
    $('#calendar').slick({
        infinite: true,
        slidesToShow: 4,
        slidesToScroll: 4,
        adaptiveHeight: true
    })
    // AJOUT DE LA CLASS IS-ACTIVE AU CLICK
    var hours = document.querySelectorAll('.calendar__hour');
    var div = document.querySelectorAll('div');
    for (var i = 0; i < hours.length; i++) {
        var hour = hours[i]
        var isActive = function() {
            this.classList.toggle('hour-is-active')
        }
        hour.addEventListener('click', isActive)
    }
});

