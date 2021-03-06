"use strict";

$(() => {
    // toolbar
    let topbar_open = 0,
        topbar = $('.topbar-toggler');
    
    topbar.on('click', function () {
        if (topbar_open === 1) {
            $('html').removeClass('topbar_open');
            topbar.removeClass('toggled');
            topbar_open = 0;
        } else {
            $('html').addClass('topbar_open');
            topbar.addClass('toggled');
            topbar_open = 1;
        }
    });
    
    // sidenav
    let nav_open = 0,
        nav_el = $('.sidenav-toggler');
    nav_el.on('click', function () {
        if (nav_open === 1) {
            $('html').removeClass('nav_open');
            nav_el.removeClass('toggled');
            nav_open = 0;
        } else {
            $('html').addClass('nav_open');
            nav_el.addClass('toggled');
            nav_open = 1;
        }
    });
    
    // sidebar
    $('body').on('click', '.quick-sidebar-toggler, .close-quick-sidebar, .quick-sidebar-overlay', (e) => {
        $(e.currentTarget).toggleClass('toggled');
        $('html').toggleClass('quick_sidebar_open');
        
        let $el;
        if (($el = $('.quick-sidebar-overlay')) && $el.length) {
            $el.remove();
        } else {
            $('<div class="quick-sidebar-overlay"></div>').insertAfter('.quick-sidebar');
        }
    });
    
    // scrollbars
    $('.sidebar .scrollbar').scrollbar();
    $('.main-panel .content-scroll').scrollbar();
    $('.quick-scroll').scrollbar();
    $('.quick-actions-scroll').scrollbar();
    
    // navigation highlight
    let buf = 0, $active = null;
    $('.sidebar a').each((i, el) => {
        let $el = $(el), href = $el.attr('href');
        
        if (location.pathname.startsWith(href) && href.length > buf) {
            buf = href.length;
            $active = $el;
        }
    });
    $active.parents('li').addClass('active').parents('.nav-item').find('[href^="#"]').click();
    
    $('[data-toggle="tooltip"]').tooltip();
    
    // select2 init
    $('[data-input] select').each((i, el) => {
        let $el = $(el);
        
        $el.select2({
            theme: 'bootstrap',
            width: '100%',
            placeholder: $el.data('placeholder') ? $el.data('placeholder') : null,
            minimumResultsForSearch: $el.data('search') ? $el.data('search') : -1,
            allowClear: $el.data('allow-clear') ? $el.data('allow-clear') : false,
        });
    });
    
    $('[data-table]').DataTable({
        'deferRender': true,
        'stateSave': true,
        'language': {
            'search': 'Поиск:',
            'lengthMenu': 'Отображать _MENU_ строк на страницу',
            'zeroRecords': 'Нет результатов',
            'info': 'Страница _PAGE_ из _PAGES_',
            'infoEmpty': 'Нет записей',
            'infoFiltered': '(проверено в _MAX_ результатах)',
            'paginate': {
                'first': 'В начало',
                'previous': 'Сюда',
                'next': 'Туда',
                'last': 'В конец'
            },
        }
    });
    
    // publication preview
    $('form [data-click="preview"]').on('click', function (e) {
        e.preventDefault();
        
        let $form = $(e.currentTarget).parents('form'),
            preview = window.open('/cup/publication/preview', 'prv', 'height=400,width=750,left=0,top=0,resizable=1,scrollbars=1');
        
        $form.attr('action', '/cup/publication/preview');
        $form.attr('target', 'prv');
        $form.submit();
        
        preview.focus();
        
        setTimeout(() => {
            $form.attr('action', '');
            $form.attr('target', '_self');
        }, 500);
    });
    
    moment.locale('ru');
    
    // modal window settings
    $.modal.defaults = {
        closeExisting: true,      // Close existing modals. Set this to false if you need to stack multiple modal instances.
        escapeClose: true,        // Allows the user to close the modal by pressing `ESC`
        clickClose: true,         // Allows the user to close the modal by clicking the overlay
        closeText: 'Close',       // Text content for the close <a> tag.
        closeClass: '',           // Add additional class(es) to the close <a> tag.
        showClose: false,         // Shows a (X) icon/link in the top-right corner
        modalClass: 'modal',      // CSS class added to the element being displayed in the modal.
        blockerClass: 'blocker',  // CSS class added to the overlay (blocker).
        spinnerHtml: '<div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div>',
        showSpinner: true,        // Enable/disable the default spinner during AJAX requests.
        fadeDuration: null,       // Number of milliseconds the fade transition takes (null means no transition)
        fadeDelay: 1.0            // Point during the overlay's fade-in that the modal begins to fade in (.5 = 50%, 1.5 = 150%, etc.)
    };
    
    // parameters guest user && user group
    {
        let $select = $('select[name="access[]"], [name="user[access][]"]'),
            $options = $select.find('option');
        
        $('[data-access-click]').on('click', (e) => {
            let $btn = $(e.currentTarget),
                type = $btn.attr('data-access-click');
            
            if (type === 'none') {
                $options.prop('selected', false);
            } else {
                $options.each((i, el) => {
                    let $buf = $(el);
                    
                    if ($buf.val().startsWith(type)) {
                        $buf.prop('selected', !(+$buf.prop('selected')));
                    }
                });
            }
            
            $select.trigger('change.select2');
        });
    }
    
    // parameters add new entity key (API key)
    {
        $('[data-entity-click="add"]').on('click', (e) => {
            function key() {
                let d = new Date().getTime();
                
                return 'xxxx-xyyx-xxxx-yxxy'.replace(/[xy]/g, (c) => {
                    let r = (d + Math.random() * 16) % 16 | 0;
                    
                    d = Math.floor(d / 16);
                    
                    return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                });
            }
            
            
            let $keys = $('[name="entity[keys]"]'),
                value = $keys.val();
            
            $keys.val((value ? value + "\n" : '') + key());
        })
    }
    
    // parameters add variables
    {
        let $hidden = $('[name="var[]"]').parents('[data-input]'),
            $value = $('[data-input="variable"]');
        
        $('[data-click="add_variable"]').on('click', () => {
            let $clone = $hidden.clone();
            
            if (!$value.val() || $('[name="var[' + $value.val() + ']"]').length) {
                $value.parents('.form-group').addClass('has-error');
                setTimeout(() => {
                    $value.parents('.form-group').removeClass('has-error');
                }, 2500);
            } else {
                $clone.find('div:first').text('var_' + $value.val());
                $clone.find('input')
                    .attr('type', 'text')
                    .attr('name', 'var[' + $value.val() + ']');
                
                $clone.insertBefore($hidden);
                $value.val('');
            }
        });
    }
    
    // product attribute
    {
        let $that = $('[id="attributes"]'),
            $row = $that.find('div.col-12.col-md-6 .row'),
            $template = $that.find('div[style="display: none"]').show().detach(),
            $select = $that.find('select');
        
        $that.find('button').on('click', () => {
            if ($row.find('[name="attributes[' + $select.val() + ']"]').length === 0) {
                let $buf = $template.clone();
                
                $buf.find('label').text($select.find(':selected').text());
                $buf.find('input').attr('name', 'attributes[' + $select.val() + ']');
                
                $buf.appendTo($row);
            }
        });
    }
    
    // product relation
    {
        let
            $that = $('[id="related"]'),
            $modal = $('[data-related-modal-products].modal'),
            $category = $modal.find('[type="select"][name="category"]'),
            $product = $modal.find('[type="select"][name="product"]'),
            $option = $('<option>'),
            $quantity = $modal.find('[type="number"]'),
            $btnSuccess = $modal.find('button'),
            $template = $that.find('.list-group [style="display: none!important;"]').show().detach()
        ;
        
        $that.find('[data-btn-related-modal-products]').click((e) => {
            e.preventDefault();
            this.blur();
            
            let handler = (e) => {
                $product
                    .html('')
                    .prop('disabled', true);
                
                $.get('/api/catalog/product', {category: $(e.currentTarget).val()}, (res) => {
                    if (res.status === 200) {
                        for (let item of res.data) {
                            $product.append(
                                $option.clone().text(item.title).val(item.uuid).data('price', item.price)
                            );
                        }
                    }
                    
                    $product
                        .trigger('change.select2')
                        .prop('disabled', false);
                });
            };
            $category.html('').off('change', handler).on('change', handler);
            
            $.get('/api/catalog/category', (res) => {
                if (res.status === 200) {
                    (function renderTree(list, parent = '00000000-0000-0000-0000-000000000000', title = '') {
                        let buf = 0;
                        
                        for (let item of list) {
                            if (item.parent === parent) {
                                let $el = $option.clone().text((title + ' ' + item.title).trim()).val(item.uuid);
                                
                                $category.append($el);
                                
                                if (renderTree(list, item.uuid, (title + ' ' + item.title).trim()) !== 0) {
                                    $el.remove();
                                }
                                
                                buf++;
                            }
                        }
                        
                        return buf;
                    })(res.data);
                    
                    $category.trigger('change').trigger('change.select2');
                    $modal.modal();
                }
            });
        });
        
        $that.find('ul.list-group button').on('click', (e) => {
            e.preventDefault();
            
            $(e.currentTarget).parents('li').remove();
        });
        
        $btnSuccess.on('click', () => {
            if ($product.val() && $quantity.val() >= 1) {
                let $selected = $product.find(':selected'),
                    $find = $('[name="relation[' + $selected.attr('value') + ']"]');
                
                if ($find.length === 0) {
                    let $buf = $template.clone(),
                        $a = $buf.find('a'),
                        $input = $buf.find('[name="relation[]"]');
                    
                    $a.attr('href', $a.attr('href').replace('%UUID%', $selected.val()));
                    $a.text($selected.text());
                    $input.attr('name', 'relation[' + $selected.attr('value') + ']');
                    $input.val($quantity.val());
                    
                    $buf.appendTo($that.find('ul.list-group'));
                } else {
                    $find.val(parseFloat($find.val()) + parseFloat($quantity.val()));
                }
                
                $.modal.close();
            }
        });
    }
    
    // order form
    {
        let
            $table = $('[data-table="order"]'),
            table = $table.DataTable(),
            $modal = $('[data-order-modal-products].modal'),
            $category = $modal.find('[type="select"][name="category"]'),
            $product = $modal.find('[type="select"][name="product"]'),
            $option = $('<option>'),
            $quantity = $modal.find('[type="number"]'),
            $btnSuccess = $modal.find('button')
        ;
        
        $table.find('tr [type="number"]').on('change', (e) => {
            let $el = $(e.currentTarget);
            
            if ($el.val() <= 0) {
                $el.parents('tr').remove();
            }
        });
        
        $('[data-btn-order-modal-products]').click((e) => {
            e.preventDefault();
            this.blur();
            
            let handler = (e) => {
                $product
                    .html('')
                    .prop('disabled', true);
                
                $.get('/api/catalog/product', {category: $(e.currentTarget).val()}, (res) => {
                    if (res.status === 200) {
                        for (let item of res.data) {
                            $product.append(
                                $option.clone().text(item.title).val(item.uuid).data('price', item.price)
                            );
                        }
                    }
                    
                    $product
                        .trigger('change.select2')
                        .prop('disabled', false);
                });
            };
            $category.html('').off('change', handler).on('change', handler);
            
            $.get('/api/catalog/category', (res) => {
                if (res.status === 200) {
                    (function renderTree(list, parent = '00000000-0000-0000-0000-000000000000', title = '') {
                        let buf = 0;
                        
                        for (let item of list) {
                            if (item.parent === parent) {
                                let $el = $option.clone().text((title + ' ' + item.title).trim()).val(item.uuid);
                                
                                $category.append($el);
                                
                                if (renderTree(list, item.uuid, (title + ' ' + item.title).trim()) !== 0) {
                                    $el.remove();
                                }
                                
                                buf++;
                            }
                        }
                        
                        return buf;
                    })(res.data);
                    
                    $category.trigger('change').trigger('change.select2');
                    $modal.modal();
                }
            });
        });
        
        $btnSuccess.on('click', () => {
            if ($product.val() && $quantity.val() >= 1) {
                let $selected = $product.find(':selected'),
                    $find = $('[name="list[' + $selected.attr('value') + ']"]');
                
                if ($find.length === 0) {
                    let $input = $('<input class="form-control" type="number" placeholder="1" min="0" step="any">')
                        .attr('name', 'list[' + $selected.attr('value') + ']')
                        .val($quantity.val())
                    ;
                    
                    table.row.add([$selected.text(), $selected.data('price'), $quantity.val()])
                        .draw(true)
                        .nodes().to$()
                        .find('td:last-child').html($('<div class="form-group">').append($input));
                } else {
                    $find.val(parseFloat($find.val()) + parseFloat($quantity.val()));
                }
                
                $.modal.close();
            }
        });
    }
    
    window.print_element = function (selector) {
        let $html = $('html').clone(),
            $invoice = $html.find(selector).html(),
            $style = $('<style>* {background-color:#FFFFFF!important;}</style>');
        
        // replace
        $html.find('body').html($invoice).append($style);
        
        // open print window
        let print = window.open('', 'Print-Window');
            print.document.open();
            print.document.write($html.html());
            print.print();
        setTimeout(() => print.close(), 10);
    };
});
