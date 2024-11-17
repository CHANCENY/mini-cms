(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Sidebar Toggler
    $('.sidebar-toggler').click(function () {
        $('.sidebar, .content').toggleClass("open");
        return false;
    });


    // Progress Bar
    $('.pg-bar').waypoint(function () {
        $('.progress .progress-bar').each(function () {
            $(this).css("width", $(this).attr("aria-valuenow") + '%');
        });
    }, {offset: '80%'});


    // Calender
    $('#calender').datetimepicker({
        inline: true,
        format: 'L'
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        items: 1,
        dots: true,
        loop: true,
        nav : false
    });


    // Worldwide Sales Chart
    var ctx1 = $("#worldwide-sales").get(0).getContext("2d");
    var metrics_data = getMetrics();
    var myChart1 = new Chart(ctx1, {
        type: "bar",
        data: {
            labels: metrics_data.time,
            datasets: metrics_data.data
            },
        options: {
            responsive: true
        }
    });

    function getMetrics() {
        const metrics = document.getElementById('metrics');
        if(metrics) {
            const data_set = JSON.parse(metrics.textContent);
            const new_data_set = [
                {
                    label: 'GET',
                    data: data_set.method.GET.time_taken || [],
                    backgroundColor: "rgba(0, 156, 255, .7)"
                },
                {
                    label: 'POST',
                    data: data_set.method.POST.time_taken || [],
                    backgroundColor: "rgba(0, 156, 255, .5)"
                },
                {
                    label: 'PUT',
                    data: data_set.method.PUT.time_taken || [],
                    backgroundColor: "rgba(0, 156, 255, .6)"
                },
                {
                    label: 'PATCH',
                    data: data_set.method.PATCH.time_taken || [],
                    backgroundColor: "rgba(0, 156, 255, .2)"
                },
                {
                    label: 'DELETE',
                    data: data_set.method.DELETE.time_taken || [],
                    backgroundColor: "rgba(0, 156, 255, .8)"
                }
            ];
            return {
                date: data_set.DATE,
                data: new_data_set,
                time: data_set.TIME
            }
        }
        return {date: [], data: []}
    }
    

    // Salse & Revenue Chart
    var ctx2 = $("#salse-revenue").get(0).getContext("2d");
    var myChart2 = new Chart(ctx2, {
        type: "line",
        data: {
            labels: ["2016", "2017", "2018", "2019", "2020", "2021", "2022"],
            datasets: [{
                    label: "Salse",
                    data: [15, 30, 55, 45, 70, 65, 85],
                    backgroundColor: "rgba(0, 156, 255, .5)",
                    fill: true
                },
                {
                    label: "Revenue",
                    data: [99, 135, 170, 130, 190, 180, 270],
                    backgroundColor: "rgba(0, 156, 255, .3)",
                    fill: true
                }
            ]
            },
        options: {
            responsive: true
        }
    });
    
})(jQuery);

