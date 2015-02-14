var wpftTestimonialOrder = wpftTestimonialOrder || {params:{},texts:{}}

jQuery(document).ready(function($, undefined){
    var _spin  = $('.wp-list-table .column-testimonial-order .dashicons')

      , _list = $('#the-list').sortable({
            cursor      : 'move'
          , cursorAt    : {left:-10, top:0}
          , cancel      : '.no-items,.inline-editor'
          , placeholder : 'sortable-placeholder'
          , revert      : false
          , helper      : function(e, item){
                var parent    = item.parent()
                  , cols      = item.children(':visible').length
                  , width     = item.find('.row-title').closest('th, td').width()
                  , helper    = []
                  , selecteds = item

                if(item.hasClass('selected')){
                    selecteds = parent.children('.selected')
                }else{
                    item.addClass('selected').siblings().removeClass('selected')
                }

                item
                .data('testimonial-order-cols', cols)
                .data('testimonial-order-selecteds', selecteds.removeClass('selected').clone())
                .show()

                selecteds
                .addClass('sorting')
                .each(function(){
                    helper.push('<div>'+$(this).find('.row-title').text()+'</div>')
                })

                return $('<div>'+helper.join('')+'</div>').data('testimonial-order-helper', helper).width(width)
            }
          , start       : function(e, ui){
                var cols = ui.item.data('testimonial-order-cols')
                  , html = ui.helper.data('testimonial-order-pos', ui.position).data('testimonial-order-helper')

                ui.item.show()
                ui.placeholder.html('<td colspan="'+cols+'">'+html.join('')+'</td>')
            }
          , stop        : function(e, ui){
                ui.item.after(ui.item.data('testimonial-order-selecteds').addClass('sorted')).remove()
                getItems()
                .filter('.sorting').remove().end()
                .removeClass('alternate')
                .filter(':nth-child(2n+1)').addClass('alternate')

                doUpdate(getIds($('#the-list .testimonial-order-id')))
            }
          , update : function(e, ui){
                ui.item.data('testimonial-order-update', true)
            }
        })

      , startUpdate = function(){
            _spin.addClass('spinner')
            _list.sortable('disable')
        }

      , doUpdate = function(ids){
            startUpdate()
            wpftTestimonialOrder.params.ids   = ids || []
            wpftTestimonialOrder.params.order = perPage * (currentPage - 1) + 1
            $.post(window.ajaxurl, wpftTestimonialOrder.params, function(r){
                var json = JSON.parse(r)

                if(json.redirect){
                    window.location.href = json.redirect
                }

                if(wpftTestimonialOrder.params.ids.length){
                    setTimeout(function(){
                        getItems().filter('.sorted').removeClass('sorted')
                    }, 300)
                }
                endUpdate()
            })
        }

      , endUpdate = function(){
            _list.sortable('enable')
            _spin.removeClass('spinner')
        }

      , getItems = function(){
            return _list.children('tr:not(.inline-editor)')
        }

      , select = function(e){
            if(e.ctrlKey || e.metaKey){
                $(this).toggleClass('selected')
            }else if(e.shiftKey){
                var items = getItems()
                  , from  = items.index(items.filter('.selected').first())
                  , to    = items.index(this)

                if(-1 == from){
                    $(this).toggleClass('selected')
                }else{
                    if(from > to){
                        to = [from, from = items.index(this)][0]
                    }
                    items.slice(from, to+1).addClass('selected')
                }
            }
        }

      , getIds = function(e){
            var ids = [];
            for(var i=0; i<e.length; i++){
                ids.push(e[i].innerHTML)
            }
            return ids.join(',')
        }

      , _pref = $('input[name="testimonial-order-hide"]', '#adv-settings')

      , _reset = $('.testimonial-order-actions .reset', '#adv-settings').on('click', function(){
            if(!$(this).hasClass('disable')){
                if(confirm(wpftTestimonialOrder.texts.confirmReset)){
                    doUpdate()
                }
            }

            return false
        })

      , refresh = function(){
            _list.sortable(_pref.prop('checked') ? 'enable' : 'disable')
            _reset[_pref.prop('checked') ? 'removeClass' : 'addClass']('disable')
        }

      , currentPage = $('.tablenav.top .pagination-links .current-page').val() || 1

      , perPage     = $('#adv-settings .screen-per-page').val()

      , revertInline = function(){
            if(undefined != window[wpftTestimonialOrder.params.inline]){
                window[wpftTestimonialOrder.params.inline].revert()
            }
      }

      , init = function(){
            $(document)
            .on('mousedown.anythig-order', '#the-list > tr:not(.inline-editor)', revertInline)
            .on('click.anythig-order', '#the-list > tr:not(.inline-editor)', select)
            .ajaxSend(function(e, xhr, o, undefined){
                if(-1 == o.data.indexOf('screen_id=') && undefined != window.pagenow){
                    o.data += '&screen_id='+window.pagenow
                }
            })

            _pref.on('click.anythig-order', refresh)

            refresh()
        }

    init()
});
