/**
 * Created by Quy on 11/10/2015.
 */
(function($, Drupal) {

    Drupal.Marketplace = Drupal.Marketplace || {};
    Drupal.behaviors.actionMarketplace = {
        attach: function(context) {
            Drupal.Marketplace.init();
            //main_menu_float();
            Drupal.Marketplace.slideshow();
            Drupal.Marketplace.carousel();
            // Drupal.Marketplace.downloadapp_block();            
            Drupal.Marketplace.elevatezoom();
            Drupal.Marketplace.setOwlCarousel();
            Drupal.Marketplace.fixLoginBlock();
            Drupal.Marketplace.hidePopup();
            Drupal.Marketplace.showPopup();
            Drupal.Marketplace.initProductQuantity();
            Drupal.Marketplace.productDetail();
            Drupal.Marketplace.fixImageSources();
            Drupal.Marketplace.mobileMenu();
            Drupal.Marketplace.main_menu_float();
            Drupal.Marketplace.customCart();
            Drupal.Marketplace.quantity();
            Drupal.Marketplace.owlcarouselcustom();
            
        }
    };
    var base_path = Drupal.url('');
    Drupal.Marketplace.init = function() {
        // Placeholder
        Drupal.Marketplace.setInputPlaceHolder('keys', 'Search...', '.block-search .form-item');
        Drupal.Marketplace.setInputPlaceHolder('mail[0][value]', 'Your email', '.block-simplenews');        

        if (base_path.length > 1) {
            Drupal.Marketplace.fixBasePath();
        }
        $('.menu--main .content > ul.menu > li.menu-item--expanded > ul > li').matchHeight();
        $('.view-products .views-row .views-field-title').matchHeight();
        $('.view-frontpage .views-row .views-field-title').matchHeight();
        ($)('#edit-category').chosen();

        $('.btn-btt').smoothScroll({
            speed: 600
        });
    }

    $(window).scroll(function() {
        if ($(window).scrollTop() > 200) {
            $('.btn-btt').show();
        } else {
            $('.btn-btt').hide();
        }
    });

    Drupal.Marketplace.main_menu_float = function() {
        var flag = false;
        var timeout;

        ($)('#block-mainnavigation-2 .content').fadeOut();
        ($)('#block-mainnavigation-2 h2').hover(function() {
            if (timeout) {
                clearTimeout(timeout);
            }

            var self = ($)(this);
            var content = self.parent().find('.content');
            content.fadeIn(200);
            flag = false;
        }, function() {
            var self = ($)(this);
            var content = self.parent().find('.content');
            timeout = setTimeout(function() {
                if (!flag) {
                    content.fadeOut(200);
                }
                if (timeout) {
                    clearTimeout(timeout);
                }
                flag = false;
            }, 200);
        });
        ($)('#block-mainnavigation-2 .content').hover(function() {
            var self = ($)(this);
            flag = true;
        }, function() {
            var self = ($)(this);
            timeout = setTimeout(function() {
                if (!flag) {
                    self.fadeOut(200);
                }
                if (timeout) {
                    clearTimeout(timeout);
                }
                flag = false;
            }, 200);
        });
    }

    /* var base_path = Drupal.url('');
     if (base_path == '/ar/') {
         fixImageSources();
     }*/
    Drupal.Marketplace.mobileMenu = function() {
      $('#button-main-menu').mobileMenu();
    }


    Drupal.Marketplace.fixImageSources = function() {
        ($)('.region-footer-fourth img').each(function(i, e) {
            var self = ($)(this);
            var src = self.attr('src');
            if (src.indexOf('arsites') != -1) {
                var tmphref = src.replace("arsites", "sites");
                self.attr('src', tmphref);
            }
        });

        /* ($)('.block-block-content7855d7a2-90af-4bf7-9100-c1f7bbd4a2bd img').each(function(i, e) {
             var self = ($)(this);
             var src = self.attr('src');
             var tmphref = src.substring(3);
             self.attr('src', tmphref);
         });*/
    }

    Drupal.Marketplace.slideshow = function() {
        ($)('.slideshow').flexslider({
            animation: 'slide',
            selector: '.view-content > .views-row',
            slideshowSpeed: 5000,
            animationSpeed: 600,
            controlNav: true,
            directionNav: false
        });
    }

    Drupal.Marketplace.hidePopup = function() {
        $('#page').once('load').click(function() {
            $('.block.show').removeClass('show');
            //Fix
            $('#block-userlogin').removeClass('show');
        });
    };

    Drupal.Marketplace.showPopup = function() {
        $('#block-yourorder').once('load').click(function(event) {
            $('.block.show').removeClass('show');
            $(this).toggleClass('show');
            event.stopPropagation();
        });
        $('#block-shoppingcart').once('load').click(function(event) {
            $('.block.show').removeClass('show');
            $(this).toggleClass('show');
            event.stopPropagation();
            return false;
        });
        //Fix
        $("#block-userlogin .content").click(function(event) {
            event.stopPropagation();
        });

        $('#block-userlogin').once('load').click(function(event) {
            $('.block.show').removeClass('show');
            $(this).toggleClass('show');
            event.stopPropagation();
        });
    }

    Drupal.Marketplace.carousel = function() {
        ($)('.carousel-list').flexslider({
            animation: 'slide',
            selector: '.view-content > .views-row',
            animationLoop: false,
            itemWidth: 210,
            /*itemMargin: 5,*/
            maxItems: 5,
            controlNav: false,
            directionNav: true
        });
    }

    Drupal.Marketplace.elevatezoom = function() {
        Drupal.Marketplace.preprocess_product_images();

        // refer to http://www.elevateweb.co.uk/image-zoom/configuration
        $('#product-image').elevateZoom({
            gallery: 'product-galaxy',
            cursor: 'pointer',
            imageCrossfade: true,
            galleryActiveClass: 'active',
        });

        // $("#product-image").on("click", function(e) {
        //     // var ez = $('#product-image').data('elevateZoom');
        //     $('#product-image').fancybox({
        //       buttons : [
        //         'zoom',
        //         'close'
        //       ]
        //     });
        //     // return false;
        // });

        // $("#product-image").bind("click", function(e) {
        //     var ez = $('#product-image').data('elevateZoom');
        //     $.fancybox(ez.getGalleryList());
        //     return false;
        // });
    }

    Drupal.Marketplace.preprocess_product_images = function() {
        var large_image = $('#product-images-wrapper > img');
        var src = large_image.attr('src');
        large_image.attr('id', 'product-image');
        if (src && src.split('?').length > 0) {
            large_image.attr('data-zoom-image', src.split('?')[0].replace("/styles/large/public", ""));
        }

        var product_image_wrappers_gallery = $('#product-galaxy a');
        product_image_wrappers_gallery.each(function(i, e) {
            var self = $(this);
            var img = self.find('img');
            var src = img.attr('src');

            if (src.split('?').length > 0) {
                self.attr('data-image', src);
                self.attr('data-zoom-image', src.split('?')[0].replace("/styles/large/public", ""));
            }
        });
    }

    Drupal.Marketplace.downloadapp_block = function() {
        $('#block-downloadapps img, #block-paymentmethod img').each(function(i, e) {
            var self = $(this);
            var src = self.attr('src');
            var location = $('#base-path').attr('href');
            src = location + src;
            self.attr('src', src);
        });
    }

    Drupal.Marketplace.setOwlCarousel = function() {
        $('.products-recommend .view-content').owlCarousel({
            items: 5,
            itemsDesktop: [1199, 4],
            itemsDesktopSmall: [979, 3],
            itemsTablet: [768, 2],
            itemsMobile: [479, 1],
            navigation: true,
            rtl: true,
        });
    }

    Drupal.Marketplace.fixBasePath = function() {
        ($)('.base-path-me').each(function(i, e) {
            var self = ($)(this);
            var href = self.attr('href');
            if (href.indexOf('/' == 0)) {
                href = href.slice(1);
            }

            href = base_path + href;
            self.attr('href', href);
        });
    }

    Drupal.Marketplace.initProductQuantity = function() {
        var instock = 10;
        var quantity = $(".commerce-add-to-cart.form-item-quantity");
        if (quantity.children('.commerce-add-to-cart .increase').length == 0) {
            quantity.append($('<span class="btn increase" id="quantity-increase"></span>'));
        }
        if (quantity.children('.commerce-add-to-cart .decrease').length == 0) {
            quantity.prepend($('<span class="btn decrease" id="quantity-decrease"></span>'));
        }
        var node_product_price = $("#main .node .field-name-field-product .form-item-quantity");
        if (node_product_price.find('.increase').length == 0) {
            node_product_price.append('<span class="btn increase" id="quantity-increase"></span>');
        }
        if (node_product_price.find('.decrease').length == 0) {
            node_product_price.prepend('<span class="btn decrease" id="quantity-decrease"></span>');
        }
        $('#quantity-increase').once('load').click(function(event) {
            var value = parseInt($(this).parent().children('input#edit-quantity').val());
            value = value + 1;
            if (value <= instock) {
                $(this).parent().children('input#edit-quantity').val(value);
                $(this).parent().children('.commerce-add-to-cart .decrease').removeClass("disabled");
            }
            event.preventDefault();
            event.stopPropagation();
        });

        $('#quantity-decrease').once('load').click(function(event) {
            var value = parseInt($(this).parent().children('input#edit-quantity').val());
            value = value - 1;
            if (value >= 1) {
                $(this).parent().children('input#edit-quantity').val(value);
                $(this).parent().children('.commerce-add-to-cart .increase').removeClass("disabled");
                if (value == 1) {
                    $(this).parent().children('.commerce-add-to-cart .decrease').addClass("disabled");
                }
            }
            event.preventDefault();
            event.stopPropagation();
        });

        var outStock = $('.out-of-stock');
        outStock.find('.form-item-quantity .form-text').prop('disabled', true);

        /* shopping cart detail */
        var cart_quantity = $('#views-form-commerce-cart-form-default .views-field-edit-quantity .form-item');

        $('#views-form-commerce-cart-form-default tbody tr').each(function() {
            var stock = $(this).find('td.views-field-edit-quantity span').hide().text();
            if ($(this).find('.increase').length == 0) {
                cart_quantity.append('<a href="javascript:void(0)" class="btn increase"></a>');
            }
            if ($(this).find('.decrease').length == 0) {
                cart_quantity.prepend('<a href="javascript:void(0)" class="btn decrease"></a>');
            }
            $(this).find('.increase').once('load').click(function() {
                var value = parseInt($(this).parent().find('input[type=text]').val()) + 1;
                if (value <= stock) {
                    $(this).parent().find('input[type=text]').val(value);
                }
            });
            $(this).find('.decrease').once('load').click(function() {
                var value = parseInt($(this).parent().find('input[type=text]').val());
                if (value > 1) {
                    value--;
                    $(this).parent().find('input[type=text]').val(value);
                }
            });
        });

    };

    Drupal.Marketplace.setInputPlaceHolder = function(name, text, selector) {
        selector = selector == undefined ? '' : selector + ' ';

        if ($.support.placeholder) {
            $(selector + 'input[name="' + name + '"]').attr('placeholder', Drupal.t(text));
        } else {
            $(selector + 'input[name="' + name + '"]').val(Drupal.t(text));
            console.log($(selector + 'input[name="' + name + '"]'));
            $(selector + 'input[name="' + name + '"]').focus(function() {
                if (this.value == Drupal.t(text)) {
                    this.value = '';
                }
            }).blur(function() {
                if (this.value == '') {
                    this.value = Drupal.t(text);
                }
            });
        }
    }

    $.support.placeholder = (function() {
        var i = document.createElement('input');
        return 'placeholder' in i;
    })();

    Drupal.Marketplace.productDetail = function() {
        $('.product-detail-tabs a').smoothScroll({
            speed: 600
        });
    };

    Drupal.Marketplace.fixLoginBlock = function() {
        if (!$('#block-userlogin').hasClass('added')) {
            $('#block-userlogin').prepend('<h2>Sign in</h2>');
            $('#block-userlogin').addClass('added');
        }
    }

    Drupal.Marketplace.customCart = function() {
        if (!$('.block-commerce-cart').hasClass('customCart')) {
            $('.block-commerce-cart').addClass('customCart');
            var item=$('.block-commerce-cart .cart-block--summary__count').text();
            var search_item=item.indexOf(' item');
            var slices_item=item.slice(0,search_item);
            $('.block-commerce-cart .cart-block--summary__count').text(slices_item);
            $('.block-commerce-cart .cart-block--summary__count').after('<span class="cart-icon">Cart</span>');
        }
        $('.cart-block--link__expand').click(function(){
            $(this).toggleClass("show");
        });
    }


    Drupal.Marketplace.quantity = function(){
      if(!$('.quantity-nav').length) {
        jQuery('<div class="quantity-nav"><div class="quantity-button quantity-up">+</div><div class="quantity-button quantity-down">-</div></div>').insertAfter('.form-type-number input');
        jQuery('.form-type-number').each(function() {
          var spinner = jQuery(this),
            input = spinner.find('input[type="number"]'),
            btnUp = spinner.find('.quantity-up'),
            btnDown = spinner.find('.quantity-down'),
            min = input.attr('min'),
            max = input.attr('max');

          btnUp.click(function() {
            var oldValue = parseFloat(input.val());
            if (oldValue >= max) {
              var newVal = oldValue;
            } else {
              var newVal = oldValue + 1;
            }
            spinner.find("input").val(newVal);
            spinner.find("input").trigger("change");
          });

          btnDown.click(function() {
            var oldValue = parseFloat(input.val());
            if (oldValue <= min) {
              var newVal = oldValue;
            } else {
              var newVal = oldValue - 1;
            }
            spinner.find("input").val(newVal);
            spinner.find("input").trigger("change");
          });

        });
      }
    };

    Drupal.Marketplace.owlcarouselcustom = function(){
        $('.carousel-affiliate-inner').owlCarousel({
            items: 1,
            itemsDesktop: [1199, 1],
            itemsDesktopSmall: [979, 1],
            itemsTablet: [768, 1],
            itemsMobile: [479, 1],
            navigation: false,
            rtl: true,
            autoplay:true,
            autoplayTimeout:1000,
            autoplayHoverPause:true,
        });
    }

})(jQuery, Drupal);