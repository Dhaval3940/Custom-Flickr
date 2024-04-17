jQuery(document).ready(function ($) {
    // Initialize slick slider for main images
    var slider = $('.slider-for').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '.slider-nav',
        adaptiveHeight: true,
        responsive: [{
            breakpoint: 768,
            settings: {
                adaptiveHeight: false
            }
        }]
    });

    // Initialize slick slider for thumbnails
    $('.slider-nav').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        asNavFor: '.slider-for',
        dots: false,
        centerMode: false,
        focusOnSelect: true,
        variableWidth: true,
        responsive: [{
                breakpoint: 768,
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });
   // Open image in full screen on click
    $('.slider-for').on('click', '.slick-slide img', function () {
        var imgUrl = $(this).attr('src');
        var fullScreenImage = '<div class="full-screen-image">' +
                                '<span class="close-btn">&times;</span>' +
                                '<span class="prev-btn">&lt;</span>' +
                                '<span class="next-btn">&gt;</span>' +
                                '<img src="' + imgUrl + '">' +
                              '</div>';
        $('body').append(fullScreenImage);
        var $fullScreenImage = $('.full-screen-image');
        $fullScreenImage.fadeIn();
        // Close image on clicking close button
        $fullScreenImage.on('click', '.close-btn', function () {
            $fullScreenImage.fadeOut(function () {
                $(this).remove();
            });
        });
    });

    // Close full-screen image on Escape key press
    $(document).on('keydown', function (e) {
        if (e.key === "Escape") {
            $('.full-screen-image').fadeOut(function () {
                $(this).remove();
            });
        }
    });
});
