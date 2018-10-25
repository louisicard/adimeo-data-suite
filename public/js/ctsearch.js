(function ($) {
  $(document).ready(function () {

    $('nav#main-menu li.expandable a').click(function(e){
      e.preventDefault();
      var ul = $(this).parents('li.expandable').find('ul');
      ul.toggle();
      $('nav#main-menu li.expandable ul').each(function(){
        if($(this)[0] != ul[0]){
          $(this).hide();
        }
      });
    });
    $('nav#main-menu li.expandable li a').unbind('click');

    $(window).click(function(e){
      if(e.clientX > 0 && e.clientY > 0 && $(e.target).parents('nav#main-menu li.expandable').size() == 0){
        $('nav#main-menu li.expandable ul').hide();
      }
    });


    $('a.index-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.DeleteIndexConfirm, function () {
        window.location = url;
      });
    });
    $('a.datasource-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.DeleteDatasourceConfirm, function () {
        window.location = url;
      });
    });
    $('a.processor-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.DeleteProcessorConfirm, function () {
        window.location = url;
      });
    });
    $('a.search-page-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.DeleteSearchPageConfirm, function () {
        window.location = url;
      });
    });
    $('a.matching-list-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.DeleteMatchingListConfirm, function () {
        window.location = url;
      });
    });

    $('#delete-mapping-link').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.DeleteMappingConfirm, function () {
        window.location = url;
      });
    });
    $('a.delete-repo').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.DeleteRepositoryConfirm, function () {
        window.location = url;
      });
    });
    $('a.delete-snapshot').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.DeleteSnapshotConfirm, function () {
        window.location = url;
      });
    });
    $('a.user-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.UserDeleteConfirm, function () {
        window.location = url;
      });
    });
    $('a.group-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.GroupDeleteConfirm, function () {
        window.location = url;
      });
    });
    $('a.autopromote-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.AutopromoteDeleteConfirm, function () {
        window.location = url;
      });
    });
    $('a.boost-query-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.BoostQueryDeleteConfirm, function () {
        window.location = url;
      });
    });
    $('a.parameter-delete').click(function (e) {
      e.preventDefault();
      var url = $(this).attr('href');
      return advConfirm(__ctsearch_js_translations.ParameterDeleteConfirm, function () {
        window.location = url;
      });
    });

    $('.search-page .search-result-source-toggler a').click(function () {
      $(this).parents('.search-result').find('.search-result-source').slideToggle();
    });

    var agg_facet_see_more_handler = function (e) {
      e.preventDefault();
      var agg_id = $(this).parents('.agg').attr('id');
      var link = $(this);
      $(this).addClass("ajax-processing");
      $.ajax({
        url: $(this).attr('href')
      }).done(function (html) {
        $('#' + agg_id + '.agg').html($(html).find('#' + agg_id + '.agg').html());
      }).complete(function () {
        $('#' + agg_id + '.agg .see-more a').click(agg_facet_see_more_handler);
      });
    };
    $('.search-page .aggregations .see-more a').click(agg_facet_see_more_handler);

    $('table.log-table tbody td.object').each(function () {
      var obj = JSON.parse($(this).html());
      $(this).html(prettyPrint(obj), {
        // Config
        expanded: false, // Expanded view (boolean) (default: true),
        maxDepth: 8 // Max member depth (when displaying objects) (default: 3)
      }).wrapInner('<div class="pretty-json" style="display:none"></div>');
      $(this).prepend('<div><a href="javascript:void(0)">Show/hide object</a></div>');
      $(this).find('a').click(function () {
        $(this).parents('td').find('.pretty-json').slideToggle();
      });
    });

    if($('table.log-table').size() > 0){
      if($('table.log-table > tbody > tr').size() == 100) {
        var link = $('<a href="#">' + __ctsearch_js_translations.Next + '</a>');
        link.insertAfter($('table.log-table'));
        link.click(function (e) {
          var qs = '';
          var url = '';
          var from = parseInt($('.log-form form').find('input#form_from').val());
          from += 100;
          $('.log-form form').find('input#form_from').val(from);
          $('.log-form form').submit();
          e.preventDefault();
        });
        link.wrap('<div class="next"></div>');
      }
      if(parseInt($('.log-form form').find('input#form_from').val()) > 0){
        link = $('<a href="#">' + __ctsearch_js_translations.Prev + '</a>');
        link.insertAfter($('table.log-table'));
        link.click(function(e){
          var qs = '';
          var url = '';
          var from = parseInt($('.log-form form').find('input#form_from').val());
          from -= 100;
          $('.log-form form').find('input#form_from').val(from);
          $('.log-form form').submit();
          e.preventDefault();
        });
        link.wrap('<div class="prev"></div>');
      }
    }

    $('.log-form form [type="submit"]').click(function(){
      $('.log-form form input#form_from').val(0);
    });

    if ($('#form_mappingDefinition').size() > 0) {
      $('#form_mappingDefinition').parents('form').bind('submit', function (e) {
        e.preventDefault();
        var form = $(this);
        if ($('#form_wipeData').is(':checked')) {
          return advConfirm(__ctsearch_js_translations.UpdateMappingWipe, function () {
            form.unbind();
            form.submit();
          });
        }
        else {
          return advConfirm(__ctsearch_js_translations.UpdateMappingNoWipe, function () {
            form.unbind();
            form.submit();
          });
        }
      });
      $('<div id="mapping-json-toggle-container"><a href="javascript:void(0)" id="mapping-json-toggle" class="json-link">' + __ctsearch_js_translations.ShowHideJSONDef + '</a></div>').insertBefore($('#form_mappingDefinition'));
      initMappingAssistant();
      $('#form_mappingDefinition').width($('#mapping-table').width());
      $('#form_mappingDefinition').css('display', 'none');
      $('#mapping-json-toggle').click(function () {
        $('#form_mappingDefinition').slideToggle();
      });
    }
    if($('#form_processor #form_datasourceName').size() > 0){
      var siblingsLink = $('<a href="#">Siblings (0)</a>');
      var updateSiblingsText = function(){
        var siblings = [];
        if($('#form_targetSiblings').val().length > 0) {
          siblings = $('#form_targetSiblings').val().split(',');
        }
        siblingsLink.text('Siblings (' + siblings.length + ')');
      };
      updateSiblingsText();
      siblingsLink.insertAfter($('#form_processor #form_datasourceName'));
      siblingsLink.click(function(e){
        e.preventDefault();

        var waitDialog = $('<div style="text-align:center;padding:50px;"><img src="' + __loading_image_url + '" /></div>').dialog({
          modal: true
        });
        var mainDialog = $('<div class="dialog-content datasource-siblings-dialog"></div>').dialog({
          modal: true,
          autoOpen: false,
          title: 'Select datasource siblings',
          width: 600,
          create: function () {
            $.ajax({
              url: __database_list_ajax_url
            }).success(function(list){
              var html = '<div class="datasource-list"><ul>';
              var selectedDS = $('#form_targetSiblings').val().split(',');
              for(var i in list){
                var ds = list[i];
                if(ds.id != __datasource_id) {
                  html += '<li><input type="checkbox" id="cb-siblings-' + ds.id + '" value="' + ds.id + '"' + (selectedDS.indexOf(ds.id) >= 0 ? ' checked="checked"' : '') + ' /><label for="cb-siblings-' + ds.id + '">' + ds.name + '</label></li>';
                }
              }
              html += '</ul><div class="actions"><button>OK</button></div></div>';
              mainDialog.html(html);
              mainDialog.find('.actions button').click(function(e){
                e.preventDefault();
                var selectedSiblings = [];
                mainDialog.find('input:checked').each(function(){
                  selectedSiblings.push($(this).val());
                });
                $('#form_targetSiblings').val(selectedSiblings.join(','));
                updateSiblingsText();
                mainDialog.dialog('destroy').remove();
              });
              $(mainDialog).dialog('open');
              $(waitDialog).dialog('destroy');
            });
          },
          close: function(){
            $(this).dialog('destroy').remove();
          }
        });
      });
      siblingsLink.wrap('<span class="actions"></span>');
    }
    if ($('#form_processor #form_definition').size() > 0) {
      $('<div id="mapping-json-toggle-container"><a href="javascript:void(0)" id="mapping-json-toggle" class="json-link">' + __ctsearch_js_translations.ShowHideJSONDef + '</a></div>').insertBefore($('#form_processor #form_definition'));
      initProcessorStack();
      $('#form_processor #form_definition').width($('#processor-stack').width());
      $('#form_processor #form_definition').css('display', 'none');
      $('#mapping-json-toggle').click(function () {
        $('#form_processor #form_definition').slideToggle();
      });
      $('#form_processor').submit(function (e) {
        if ($(this).find('.stack-item.error').size() > 0) {
          advAlert('Your processor needs some fixing');
          return false;
        }
        else {
          return true;
        }
      });
    }
    if ($('#form_matching_list #form_list').size() > 0) {
      $('<div id="matching-list-json-toggle-container"><a href="javascript:void(0)" id="matching-list-json-toggle" class="json-link">' + __ctsearch_js_translations.ShowHideJSONDef + '</a></div>').insertBefore($('#form_matching_list #form_list'));
      initMatchingListAssistant();
      $('#form_matching_list #form_list').width($('#matching-list-table').width());
      $('#form_matching_list #form_list').css('display', 'none');
      $('#matching-list-json-toggle').click(function () {
        $('#form_matching_list #form_list').slideToggle();
      });
    }

    $('#matching-list-size-selector + a').click(function (e) {
      e.preventDefault();
      if ($('#matching-list-field-selector').val() != '' && $('#matching-list-size-selector').val() != '') {
        window.location = $(this).attr('href') + '&field=' + encodeURIComponent($('#matching-list-field-selector').val()) + '&size=' + encodeURIComponent($('#matching-list-size-selector').val());
      }
      else {
        advAlert('You must select a field and a maximum');
      }
    });

    $(window).load(reactResponsive);
    $(window).resize(reactResponsive);

    $('.search-page .search-result .more-like-this a').click(function () {
      var container = $(this).parent();
      var link = $(this);
      if ($(this).hasClass('collapse')) {
        $(this).removeClass('collapse');
        $(this).text('See more like this');
        container.removeClass('with-children');
        container.find('.search-result').detach();
      }
      else {
        $(this).addClass('ajax-link');
        $(this).addClass('ajax-processing');

        var searchPageId = $(this).parents('*[search-page-id]').attr('search-page-id');
        var docId = $(this).parents('*[doc-id]').attr('doc-id');
        var type = $(this).parents('*[doc-type]').attr('doc-type');
        $.ajax({
          url: __base_url + 'search-pages/more-like-this/' + searchPageId + '/' + docId + '/' + type
        }).success(function (data) {
          link.removeClass('ajax-link');
          link.removeClass('ajax-processing');
          link.text('Hide');
          link.addClass('collapse');
          if ($(data).find('.search-results').children().size() > 0) {
            container.addClass('with-children');
          }
          $(data).find('.search-results').children().each(function () {
            $(this).appendTo(container);
          });
        });
      }
    });
    if ($('#search-page-form input[type="text"]').size() > 0 && __autocomplete_enabled) {
      $('#search-page-form input[type="text"]').autocomplete({
        source: function (request, response) {
          var searchPageId = $('*[search-page-id]').attr('search-page-id');
          $.ajax({
            url: __base_url + 'search-pages/autocomplete/' + searchPageId + '/' + encodeURIComponent(request.term)
          }).success(function (data) {
            if (typeof console !== 'undefined') {
              console.log('AC took : ' + data.took + 'ms');
            }
            response(data.data);
          });
        },
        select: function (event, ui) {
          $('#search-page-form input[type="text"]').val(ui.item.value);
          $('#search-page-form').submit();
        }
      });
    }

    $('ul li.index-mapping').each(function () {
      var indexName = $(this).parents('tr').find('td:first-child').text();
      var mappingName = $(this).find('a').text();
      $(this).addClass('ajax-loading');
      var li = $(this);
      $.ajax({
        url: __ctsearch_base_url + 'indexes/mapping-stat/' + indexName + '/' + mappingName
      }).success(function (data) {
        li.removeClass('ajax-loading');
        li.find('.mapping-stat').html('<ul><li>' + data.docs + '<span> documents</span></li><li>' + data.fields + '<span> fields</span></li></ul>');
      });
    });

    if ($('body.page-console .facets').size() > 0) {
      eval('var json = ' + $('body.page-console .facets').text());
      $('body.page-console .facets').JSONView(json);
    }
    $('body.page-analytics form#stat-form').submit(function (e) {
      e.preventDefault();
      var mapping = $('body.page-analytics form#stat-form select#mapping-choice').val();
      var date_from = $('body.page-analytics form#stat-form input#date-from').val();
      var date_to = $('body.page-analytics form#stat-form input#date-to').val();
      var stat = $('body.page-analytics form#stat-form select#stat-choice').val();
      var granularity = $('body.page-analytics form#stat-form select#granularity').val();
      $('body.page-analytics #stat-display').addClass('loading');
      $('body.page-analytics #stat-display').html('Loading. Please wait.');
      $('body.page-analytics #stat-display').removeClass("chart-loaded");
      $('body.page-analytics #table-stat-display').html('');
      $.ajax({
        url: __ctsearch_base_url + 'analytics/compile',
        method: 'post',
        data: 'mapping=' + encodeURIComponent(mapping) + '&date_from=' + encodeURIComponent(date_from) + '&date_to=' + encodeURIComponent(date_to) + '&stat=' + encodeURIComponent(stat) + '&granularity=' + encodeURIComponent(granularity)
      }).fail(function () {

      }).success(function (data) {
        $('body.page-analytics #stat-display').removeClass('loading');
        $('body.page-analytics #stat-display').html('');
        eval(data.jsData);
        eval('var chart = new ' + data.googleChartClass + '(document.getElementById("stat-display"));');
        if (typeof statData !== 'undefined' && typeof chartOptions !== 'undefined' && data.data.length > 0) {
          $('body.page-analytics #stat-display').addClass("chart-loaded");
          chart.draw(statData, chartOptions);
        }
        if (data.data.length > 0) {

          var html = '<table><thead><tr>';
          for (var i = 0; i < data.headers.length; i++) {
            html += '<th>' + data.headers[i] + '</th>';
          }
          html += '</tr></thead><tbody>';
          for (var i = 0; i < data.data.length; i++) {
            html += '<tr>';
            for (var j = 0; j < data.data[i].length; j++) {
              html += '<td>' + data.data[i][j] + '</td>';
            }
            html += '</tr>';
          }
          html += '</tbody></table>'
          $('body.page-analytics #table-stat-display').html(html);
        }
        else{
          $('body.page-analytics #table-stat-display').html('No data available');
        }
      });
    });

    if($('#form_search_page').size() > 0){
      initSearchPageConfigurator();

      $('#form_search_page #form_mapping').change(function(){
        initSearchPageConfigurator();
      });
    }

    $('<div id="dynamic-tpl-json-toggle-container"><a href="javascript:void(0)" id="dynamic-tpl-json-toggle" class="json-link">' + __ctsearch_js_translations.ShowHideJSONDef + '</a></div>').insertBefore(
      $('#form_dynamicTemplates'));
    $('#form_dynamicTemplates').css('width', '100%');
    $('#form_dynamicTemplates').hide();
    $('#dynamic-tpl-json-toggle').click(function(e){
      e.preventDefault();
      $('#form_dynamicTemplates').slideToggle();
    })


    $('#form_autopromote #form_index').change(function(){
      var index = $(this).val();
      $('#form_analyzer').attr('disabled', 'disabled');
      $.ajax({
        url: __autopromote_ajax_get_analyzers_url + '?index=' + index
      }).success(function(data){
        $('#form_analyzer').removeAttr('disabled');
        $('#form_analyzer').html('');
        $('#form_analyzer').append($('<option value="">Select &gt;</option>'));
        for(var i in data.analyzers){
          $('#form_analyzer').append($('<option value="' + data.analyzers[i] + '">' + data.analyzers[i] + '</option>'));
        }
        if(!data.enabled){
          $('#form_analyzer').attr('disabled', 'disabled');
          $('#form_analyzer').val(data.value);
        }
        else{
          $('#form_analyzer').removeAttr('disabled');
        }
      });
    });

    if($('#datasource-output').size() > 0){
      var id = $('#datasource-output').attr('data-datasource-id');
      var running = false;
      setInterval(function(){
        if(!running && !$('#datasource-output').is(':focus')){
          running = true;
          try {
            $.ajax({
              url: __datasource_output_ajax_url + '?id=' + encodeURIComponent(id) + '&from=' + $('#datasource-output').val().length
            }).success(function (data) {
              $('#datasource-output').val($('#datasource-output').val() + data);
              if (!$('#datasource-output').is(':focus')) {
                document.getElementById('datasource-output').scrollTop = document.getElementById('datasource-output').scrollHeight;
              }
              running = false;
            });
          }catch(e){
            running = false;
          }
        }
      }, 500);
    }

  });

  function reactResponsive() {
    if ($(window).width() > 720) {
      $('.search-page.with-aggregations .aggregations h2').detach();
      $('.search-page.with-aggregations .aggregations .agg-wrapper').css('display', 'block');
    }
    else {
      if ($('.search-page.with-aggregations .aggregations h2').size() == 0) {
        $('.search-page.with-aggregations .aggregations').prepend('<h2>Filters</h2>');
        $('.search-page.with-aggregations .aggregations h2').click(function () {
          $('.search-page.with-aggregations .aggregations .agg-wrapper').slideToggle();
        });
      }
      if ($('.search-page.with-aggregations .aggregations .agg-wrapper').size() == 0) {
        $('.search-page.with-aggregations .aggregations .agg').wrapAll('<div class="agg-wrapper"></div>');
        $('.search-page.with-aggregations .aggregations .agg-wrapper').css('display', 'none');
      }
    }
  }

  function initMappingAssistant() {
    $('#mapping-table').detach();
    var table = $('<table id="mapping-table"><thead><tr><th>' + __ctsearch_js_translations.FieldName + '</th><th>' + __ctsearch_js_translations.FieldType + '</th><th>' + __ctsearch_js_translations.FieldFormat + '</th><th>' + __ctsearch_js_translations.FieldAnalysis + '</th><th>' + __ctsearch_js_translations.FieldIncludeRaw + '</th><th>' + __ctsearch_js_translations.FieldIncludeTransliterated + '</th><th>' + __ctsearch_js_translations.FieldStore + '</th><th>' + __ctsearch_js_translations.FieldBoost + '</th><th>&nbsp;</th></tr></thead><tbody></tbody></table>').insertBefore($('#mapping-json-toggle-container'));
    var json = JSON.parse($('#form_mappingDefinition').val());
    if (json.length == 0) {
      json = {};
      $('#form_mappingDefinition').val('{}');
    }
    for (var field in json) {
      var type = json[field].type;
      var store = typeof json[field].store !== 'undefined' && !json[field].store ? __ctsearch_js_translations.FieldNotStored : __ctsearch_js_translations.FieldStored;
      var format = typeof json[field].format !== 'undefined' ? json[field].format : '-';
      var analyzed = typeof json[field].analyzer !== 'undefined' ? __ctsearch_js_translations.FieldAnalyzed : __ctsearch_js_translations.FieldNotAnalyzed;
      var analyzer = typeof json[field].analyzer !== 'undefined' ? json[field].analyzer : null;
      var includeRaw = typeof json[field].fields !== 'undefined' && typeof json[field].fields.raw !== 'undefined';
      var includeTransliterated = typeof json[field].fields !== 'undefined' && typeof json[field].fields.transliterated !== 'undefined';
      var boost = typeof json[field].boost !== 'undefined' ? json[field].boost : '1';
      table.find('tbody').append('<tr><td>' + field + '</td><td>' + type + '</td><td>' + format + '</td><td>' + analyzed + (analyzer != null ? ' (' + analyzer + ')' : '') + '</td><td>' + (includeRaw ? __ctsearch_js_translations.Yes : __ctsearch_js_translations.No) + '</td><td>' + (includeTransliterated ? __ctsearch_js_translations.Yes : __ctsearch_js_translations.No) + '</td><td>' + store + '</td><td>' + boost + '</td><td><a href="javascript:void(0)" class="mapping-delete-field action-delete">' + __ctsearch_js_translations.FieldDelete + '</a></td></tr>');
    }
    var type_select = '<select id="mapping-definition-field-type" tabindex="2">';
    type_select += '<option value="">' + __ctsearch_js_translations.FieldType + '</option>';
    for (var i = 0; i < __field_types.length; i++) {
      type_select += '<option value="' + __field_types[i] + '">' + __field_types[i] + '</option>';
    }
    type_select += '</select>';
    var format_select = '<select id="mapping-definition-field-format" disabled="disabled" tabindex="3">';
    format_select += '<option value="">' + __ctsearch_js_translations.FieldFormat + '</option>';
    for (var i = 0; i < __date_formats.length; i++) {
      format_select += '<option value="' + __date_formats[i] + '">' + __date_formats[i] + '</option>';
    }
    format_select += '</select>';
    var analysis_select = '<select id="mapping-definition-field-analysis" tabindex="4">';
    analysis_select += '<option value="not_analyzed">' + __ctsearch_js_translations.FieldNotAnalyzed + '</option>';
    for (var i = 0; i < __index_analyzers.length; i++) {
      analysis_select += '<option value="' + __index_analyzers[i] + '">' + __ctsearch_js_translations.FieldAnalyzed + ' (analyzer = ' + __index_analyzers[i] + ')</option>';
    }
    analysis_select += '</select>';
    var store_select = '<select id="mapping-definition-field-store" tabindex="5">';
    store_select += '<option value="true">' + __ctsearch_js_translations.FieldStored + '</option>';
    store_select += '<option value="false">' + __ctsearch_js_translations.FieldNotStored + '</option>';
    store_select += '</select>';
    var boost_select = '<select id="mapping-definition-field-boost" tabindex="6">';
    boost_select += '<option value="1">' + __ctsearch_js_translations.FieldBoost + '</option>';
    for (var i = 1; i <= 10; i++) {
      boost_select += '<option value="' + i + '">' + i + '</option>';
    }
    boost_select += '</select>';
    table.find('tbody').append('<tr><td><input type="text" id="mapping-definition-field-name" placeholder="' + __ctsearch_js_translations.FieldName + '" tabindex="1" /><br /><a href="javascript:void(0)" id="mapping-add-field" tabindex="7">' + __ctsearch_js_translations.FieldAdd + '</a></td><td>' + type_select + '</td><td>' + format_select + '</td><td>' + analysis_select + '</td><td><input id="mapping-definition-field-include-raw" type="checkbox" disabled="disabled" /></td><td><input id="mapping-definition-field-include-transliterated" type="checkbox" disabled="disabled" /></td><td>' + store_select + '</td><td>' + boost_select + '</td><td></td></tr>');
    table.wrap('<div class="mapping-table-container"></div>');
    $('#mapping-definition-field-type, #mapping-definition-field-analysis').change(function(){
      if($('#mapping-definition-field-analysis').val() == 'not_analyzed'){
        $('#mapping-definition-field-include-raw').attr('disabled', 'disabled');
        $('#mapping-definition-field-include-raw').removeAttr('checked');
        $('#mapping-definition-field-include-transliterated').attr('disabled', 'disabled');
        $('#mapping-definition-field-include-transliterated').removeAttr('checked');
      }
      else{
        $('#mapping-definition-field-include-raw').removeAttr('disabled');
        $('#mapping-definition-field-include-transliterated').removeAttr('disabled');
      }
    });
    $('#mapping-add-field').click(function () {
      var field_name = $('#mapping-definition-field-name').val();
      var field_type = $('#mapping-definition-field-type').val();
      if (field_name != '' && field_type != '') {
        if (typeof json[field_name] === 'undefined') {
          json[field_name] = {
            'type': field_type
          };
          if ($('#mapping-definition-field-format').val() != '') {
            json[field_name].format = $('#mapping-definition-field-format').val();
          }
          if ($('#mapping-definition-field-analysis').val() != '') {
            if ($('#mapping-definition-field-analysis').val() != 'not_analyzed') {
              json[field_name].analyzer = $('#mapping-definition-field-analysis').val();
            }
          }
          if($('#mapping-definition-field-analysis').val() == 'not_analyzed' && __elastic_server_version < 5){
            json[field_name].index = "not_analyzed";
          }
          if($('#mapping-definition-field-analysis').val() != 'not_analyzed'){
            if($('#mapping-definition-field-include-raw').is(':checked')) {
              if(typeof json[field_name].fields === 'undefined')
                json[field_name].fields = {};
              if(__elastic_server_version < 5) {
                json[field_name].fields.raw = {
                  type: "string",
                  index: "not_analyzed",
                  store: true
                };
              }
              else{
                json[field_name].fields.raw = {
                  type: "keyword",
                  store: true
                };
              }
            }
          }
          if($('#mapping-definition-field-analysis').val() != 'not_analyzed'){
            if($('#mapping-definition-field-include-transliterated').is(':checked')) {
              if(typeof json[field_name].fields === 'undefined')
                json[field_name].fields = {};
              json[field_name].fields.transliterated = {
                type: "string",
                analyzer: "transliterator",
                store: true
              };
            }
          }
          json[field_name].store = $('#mapping-definition-field-store').val() != 'false';
          if ($('#mapping-definition-field-boost').val() != '1') {
            json[field_name].boost = $('#mapping-definition-field-boost').val();
          }
          $('#form_mappingDefinition').val(JSON.stringify(json));
          initMappingAssistant();
        }
        else {
          advAlert(__ctsearch_js_translations.FieldAlreadyExists);
        }
      }
      else {
        advAlert(__ctsearch_js_translations.FieldMissingNameOrType);
      }
    });
    $('.mapping-delete-field').click(function () {
      var field_name = $(this).parents('tr').find('td:first-child').html();
      delete json[field_name];
      $('#form_mappingDefinition').val(JSON.stringify(json));
      initMappingAssistant();
    });
    $('#mapping-definition-field-type').change(function () {
      if ($(this).val() == 'date') {
        $('#mapping-definition-field-format').removeAttr('disabled');
      }
      else {
        $('#mapping-definition-field-format').attr('disabled', 'disabled');
        $('#mapping-definition-field-format').val('');
      }
    });
  }

  function initProcessorStack() {
    $('#processor-stack').detach();
    var stack = $('<div id="processor-stack" class="clearfix"></div>').insertBefore($('#mapping-json-toggle-container'));
    var json = JSON.parse($('#form_processor #form_definition').val());
    for (var i = 0; i < __datasource_fields.length; i++) {
      if ($.inArray(__datasource_fields[i], json.datasource.fields, 0) < 0) {
        json.datasource.fields.push(__datasource_fields[i]);
      }
    }
    for (var i = json.datasource.fields.length - 1; i >= 0; i--) {
      if ($.inArray(json.datasource.fields[i], __datasource_fields, 0) < 0)
        json.datasource.fields.splice(i, 1);
    }
    var available_inputs = [];
    var ds_html = '<div class="datasource stack-item"><div class="inside"><div class="name">Datasource</div><div class="display-name  ">' + json.datasource.name + '</div>';
    ds_html += '<div class="fields"><div class="legend">Output</div><ul>';
    for (var i = 0; i < json.datasource.fields.length; i++) {
      available_inputs.push('datasource.' + json.datasource.fields[i]);
      ds_html += '<li><em>' + json.datasource.fields[i] + '</em></li>';
    }
    ds_html += '</ul></div>';
    ds_html += '</div></div>';
    stack.append(ds_html);

    var error_filters = [];
    for (var i = 0; i < json.filters.length; i++) {
      var filters_html = '';
      filters_html = '<div class="filter stack-item" id="filter-' + json.filters[i].id + '"><div class="inside"><div class="edit-filter"><a href="javascript:void(0);">Edit</a></div><div class="move-filter"><a href="javascript:void(0);" class="move-left">&lt;</a><a href="javascript:void(0);" class="move-right">&gt;</a></div><div class="filter-id">ID = ' + json.filters[i].id + '</div><div class="remove-filter"><a href="javascript:void(0);">Remove</a></div><div class="name">Filter #' + (i + 1) + '</div><div class="in-stack-name">' + (typeof json.filters[i].inStackName != 'undefined' ? json.filters[i].inStackName : '') + '</div><div class="display-name">' + json.filters[i].filterDisplayName + '</div>';

      filters_html += '<div class="fields"><div class="legend">Input</div>';
      if (json.filters[i].arguments.length > 0) {
        filters_html += '<ul>';
        var error = false;
        for (var j = 0; j < json.filters[i].arguments.length; j++) {
          if ($.inArray(json.filters[i].arguments[j].value, available_inputs) < 0 && json.filters[i].arguments[j].value != 'empty_value') {
            error_filters.push('#filter-' + json.filters[i].id);
            error = true;
          }
          else {
            error = false;
          }
          filters_html += '<li' + (error ? ' class="error"' : '') + '><em>' + json.filters[i].arguments[j].key + '</em> : ' + json.filters[i].arguments[j].value + '</li>';
        }
        filters_html += '</ul>';
      }
      else {
        filters_html += '<div class="no-arguments">No input</div>';
      }
      filters_html += '</div>';

      filters_html += '<div class="fields"><div class="legend">Output</div><ul>';
      for (var j = 0; j < json.filters[i].fields.length; j++) {
        available_inputs.push('filter_' + json.filters[i].id + '.' + json.filters[i].fields[j]);
        filters_html += '<li><em>' + json.filters[i].fields[j] + '</em></li>';
      }
      filters_html += '</ul></div>';

      filters_html += '</div></div>';
      stack.append(filters_html);
    }

    $('#processor-stack').sortable({
      items: ".filter.stack-item",
      stop: function () {
        var filters = json.filters;
        json.filters = [];
        $('#processor-stack .filter.stack-item').each(function () {
          var getFilterById = function (id) {
            for (var i = 0; i < filters.length; i++) {
              if (filters[i].id == id)
                return filters[i];
            }
            return null;
          };
          var id = $(this).attr('id').split('-')[1];
          json.filters.push(getFilterById(id));
        });
        $('#form_processor #form_definition').val(JSON.stringify(json, null, 2));
        initProcessorStack();
      }
    });

    for (var i = 0; i < error_filters.length; i++) {
      $(error_filters[i]).addClass('error');
    }

    var add_filter_html = '<div id="add-filter-container" class="actions">';
    add_filter_html += '<select><option value="">Select a filter</option>';
    for (var i = 0; i < __filter_types.length; i++) {
      add_filter_html += '<option value="' + __filter_types[i].split('#')[0] + '">' + __filter_types[i].split('#')[1] + '</option>';
    }
    add_filter_html += '</select>';
    add_filter_html += '<a href="javascript:void(0)">Add filter</a>';
    add_filter_html += '</div>';
    stack.append(add_filter_html);
    $('#processor-stack #add-filter-container a').click(function () {
      if ($('#processor-stack #add-filter-container select').val() != '') {
        displayFilterSettings(json, $('#processor-stack #add-filter-container select').val(), null);
      }
      else {
        advAlert('You must select a filter type');
      }
    });
    $('#processor-stack .stack-item .edit-filter a').click(function () {
      var id = $(this).parents('.stack-item').attr('id').split('-')[1];
      for (var i = 0; i < json.filters.length; i++) {
        if (json.filters[i].id == id) {
          displayFilterSettings(json, json.filters[i].class, json.filters[i]);
          break;
        }
      }
    });
    $('#processor-stack .stack-item .remove-filter a').click(function () {
      var id = $(this).parents('.stack-item').attr('id').split('-')[1];
      for (var i = 0; i < json.filters.length; i++) {
        if (json.filters[i].id == id) {
          json.filters.splice(i, 1);
          $('#form_processor #form_definition').val(JSON.stringify(json, null, 2));
          initProcessorStack();
          break;
        }
      }
    });
    $('#processor-stack .stack-item .move-filter a.move-left').click(function () {
      var id = $(this).parents('.stack-item').attr('id').split('-')[1];
      for (var i = 0; i < json.filters.length; i++) {
        if (json.filters[i].id == id) {
          if (i >= 1) {
            var before = json.filters[i - 1];
            json.filters[i - 1] = json.filters[i];
            json.filters[i] = before;
          }
          $('#form_processor #form_definition').val(JSON.stringify(json, null, 2));
          initProcessorStack();
          break;
        }
      }
    });
    $('#processor-stack .stack-item .move-filter a.move-right').click(function () {
      var id = $(this).parents('.stack-item').attr('id').split('-')[1];
      for (var i = 0; i < json.filters.length; i++) {
        if (json.filters[i].id == id) {
          if (i <= json.filters.length - 2) {
            var after = json.filters[i + 1];
            json.filters[i + 1] = json.filters[i];
            json.filters[i] = after;
          }
          $('#form_processor #form_definition').val(JSON.stringify(json, null, 2));
          initProcessorStack();
          break;
        }
      }
    });

    if (typeof json.mapping == 'undefined') {
      json['mapping'] = {};
    }
    var mapping_html = '<div class="mapping-container"><h2>Mapping</h2>';
    mapping_html += '<table id="mapping-table"><thead><tr><th>Input</th><th>Target</th></tr></thead><tbody>';
    mapping_html += '<tr><td class="input">' + getMappingInputSelect(json, '_id') + '</td><td class="target">' + __mapping_name + '._id</td></tr>';
    for (var i = 0; i < __target_fields.length; i++) {
      mapping_html += '<tr><td class="input">' + getMappingInputSelect(json, __target_fields[i]) + '</td><td class="target">' + __mapping_name + '.' + __target_fields[i] + '</td></tr>';
    }
    mapping_html += '</tbody></table></div>';
    stack.append(mapping_html);
    stack.find('.mapping-select').change(function () {
      var target_field = $(this).attr('id').split('-')[1];
      json.mapping[target_field] = $(this).val();
      $('#form_processor #form_definition').val(JSON.stringify(json, null, 2));
    });
  }

  function getMappingInputSelect(json, target_field) {
    var html = '<select id="mapping_select-' + target_field + '" class="mapping-select"><option value="">No input</option>';
    var found_in_mapping = false;
    for (var i = 0; i < json.datasource.fields.length; i++) {
      var selected = typeof json.mapping[target_field] != 'undefined' && json.mapping[target_field] == 'datasource.' + json.datasource.fields[i];
      if (selected)
        found_in_mapping = true;
      html += '<option value="datasource.' + json.datasource.fields[i] + '"' + (selected ? ' selected="selected"' : '') + '>Datasource field &quot;' + json.datasource.fields[i] + '&quot;</option>';
    }
    for (var i = 0; i < json.filters.length; i++) {
      for (var j = 0; j < json.filters[i].fields.length; j++) {
        var selected = typeof json.mapping[target_field] != 'undefined' && json.mapping[target_field] == 'filter_' + json.filters[i].id + '.' + json.filters[i].fields[j];
        if (selected)
          found_in_mapping = true;
        html += '<option value="filter_' + json.filters[i].id + '.' + json.filters[i].fields[j] + '"' + (selected ? ' selected="selected"' : '') + '>Filter #' + (i + 1) + ' (' + json.filters[i].inStackName + ') field &quot;' + json.filters[i].fields[j] + '&quot;</option>';
      }
    }
    html += '</select>';
    if (!found_in_mapping) {
      json.mapping[target_field] = '';
      $('#form_processor #form_definition').val(JSON.stringify(json, null, 2));
    }
    return html;
  }

  function displayFilterSettings(json, filterClass, filter) {
    var waitDialog = $('<div style="text-align:center;padding:50px;"><img src="' + __loading_image_url + '" /></div>').dialog({
      modal: true
    });
    var mainDialog = $('<div class="dialog-content filter-dialog"></div>').dialog({
      modal: true,
      autoOpen: false,
      title: 'Filter settings',
      width: 600,
      create: function () {
        var dialog = $(this);
        var data = {
          class: filterClass
        };
        if (filter != null) {
          var filter_data = {};
          for (var k in filter.settings) {
            filter_data['setting_' + k] = filter.settings[k];
          }
          for (var i = 0; i < filter.arguments.length; i++) {
            filter_data['arg_' + filter.arguments[i].key] = filter.arguments[i].value;
          }
          if (filter.inStackName != 'undefined') {
            filter_data['in_stack_name'] = filter.inStackName;
          }
          if (filter.autoImplode != 'undefined') {
            filter_data['autoImplode'] = filter.autoImplode;
          }
          if (filter.autoImplodeSeparator != 'undefined') {
            filter_data['autoImplodeSeparator'] = filter.autoImplodeSeparator;
          }
          if (filter.autoStriptags != 'undefined') {
            filter_data['autoStriptags'] = filter.autoStriptags;
          }
          if (filter.isHTML != 'undefined') {
            filter_data['isHTML'] = filter.isHTML;
          }
          data['data'] = JSON.stringify(filter_data);
        }
        $.ajax({
          method: 'POST',
          url: __proc_settings_ajx_form_url,
          data: data
        }).done(function (data) {
          dialog.html(data);
          dialog.find('input.filter-argument').each(function () {
            setFilterSelect($(this), json, filter);
          });
          if (filterClass == 'CtSearchBundle\\Processor\\DebugFilter') {
            dialog.find('#form_setting_fields_to_dump').css('display', 'none');
            for (var i = 0; i < json.datasource.fields.length; i++) {
              dialog.find('#form_setting_fields_to_dump').parent().append('<div class="debug-dump-item"><input type="checkbox" id="dump-datasource_' + json.datasource.fields[i] + '" value="datasource.' + json.datasource.fields[i] + '" /><label for="dump-datasource_' + json.datasource.fields[i] + '">Datasource field ' + json.datasource.fields[i] + '</label></div>');
            }
            for (var i = 0; i < json.filters.length; i++) {
              if (filter != null && json.filters[i].id == filter.id) {
                break;
              }
              for (var j = 0; j < json.filters[i].fields.length; j++) {
                dialog.find('#form_setting_fields_to_dump').parent().append('<div class="debug-dump-item"><input type="checkbox" id="dump-filter_' + json.filters[i].id + '_' + json.filters[i].fields[j] + '" value="filter_' + json.filters[i].id + '.' + json.filters[i].fields[j] + '" /><label for="dump-filter_' + json.filters[i].id + '_' + json.filters[i].fields[j] + '">Filter #' + (i + 1) + ' (' + json.filters[i].inStackName + ') field ' + json.filters[i].fields[j] + '</label></div>');
              }
            }
            var fields = dialog.find('#form_setting_fields_to_dump').val().split(',');
            for (var i = 0; i < fields.length; i++) {
              dialog.find('#form_setting_fields_to_dump').parent().find('.debug-dump-item input[value="' + fields[i] + '"]').attr('checked', 'checked');
            }
            dialog.find('#form_setting_fields_to_dump').parent().find('.debug-dump-item input').click(function () {
              var vals = [];
              dialog.find('#form_setting_fields_to_dump').parent().find('.debug-dump-item input:checked').each(function () {
                vals.push($(this).val());
              });
              dialog.find('#form_setting_fields_to_dump').val(vals.join(','));
            });
          }
          $(mainDialog).dialog('open');
          $(waitDialog).dialog('destroy');
          dialog.find('form').submit(function (e) {
            e.preventDefault();
            $(this).append('<input type="hidden" name="class" value="' + filterClass + '" />');
            var formData = $(this).serialize();
            $(this).html('<div style="text-align:center;padding:50px;"><img src="' + __loading_image_url + '" /></div>');
            $.ajax({
              method: $(this).attr('method'),
              url: __proc_settings_ajx_form_url,
              data: formData,
              dataType: 'json'
            }).done(function (r) {
              dialog.dialog('destroy');
              if (filter == null) {
                r['id'] = Math.round(Math.random() * 100000) + '';
                json.filters.push(r);
              }
              else {
                for (var i = 0; i < json.filters.length; i++) {
                  if (json.filters[i].id == filter.id) {
                    r['id'] = filter.id;
                    json.filters[i] = r;
                    break;
                  }
                }
              }
              $('#form_processor #form_definition').val(JSON.stringify(json, null, 2));
              initProcessorStack();
            });
          });
        });
      }
    });
  }

  function setFilterSelect(input, json, filter) {
    var html = '<select id="' + input.attr('id') + '" name="' + input.attr('name') + '" required="required" class="' + input.attr('class') + '">';
    html += '<option value="">Select a source</option><option value="empty_value">Empty value</option>';
    for (var i = 0; i < json.datasource.fields.length; i++) {
      html += '<option value="datasource.' + json.datasource.fields[i] + '">Datasource field &quot;' + json.datasource.fields[i] + '&quot;</option>';
    }
    for (var i = 0; i < json.filters.length; i++) {
      if (filter != null && filter.id == json.filters[i].id) {
        break;
      } else {
        for (var j = 0; j < json.filters[i].fields.length; j++) {
          html += '<option value="filter_' + json.filters[i].id + '.' + json.filters[i].fields[j] + '">Filter #' + (i + 1) + ' (' + json.filters[i].inStackName + ') field &quot;' + json.filters[i].fields[j] + '&quot;</option>';
        }
      }
    }
    html += '</select>';
    var value = input.val();
    var id = input.attr('id');
    $(html).appendTo(input.parent()).val(value);
    input.detach();
  }

  function initMatchingListAssistant() {
    $('#matching-list-table').detach();
    var table = $('<table id="matching-list-table"><thead><tr><th>Input</th><th>Output</th><th>&nbsp;</th></tr></thead><tbody></tbody></table>').insertBefore($('#matching-list-json-toggle-container'));
    var json = JSON.parse($('#form_matching_list #form_list').val());
    for (var key in json) {
      table.find('tbody').append('<tr><td>' + key + '</td><td>' + json[key] + '</td><td><a href="javascript:void(0);" class="delete action-delete">Delete</a></td></tr>');
    }
    table.find('tbody').append('<tr><td><input type="text" id="matching-list-key" /></td><td><input type="text" id="matching-list-value" /></td><td class="actions"><a href="javascript:void(0);" class="add">Add</a></td></tr>');
    table.wrap('<div class="matching-list-table-container"></div>');
    table.find('a.delete').click(function () {
      delete json[$(this).parents('tr').find('td:first-child').html()];
      $('#form_matching_list #form_list').val(JSON.stringify(json));
      initMatchingListAssistant();
    });
    table.find('a.add').click(function () {
      if ($('#matching-list-key').val() != '') {
        if (typeof json[$('#matching-list-key').val()] == 'undefined') {
          json[$('#matching-list-key').val()] = $('#matching-list-value').val();
          $('#form_matching_list #form_list').val(JSON.stringify(json));
          initMatchingListAssistant();
        } else {
          advAlert('Input key "' + $('#matching-list-key').val() + '" is already defined.');
        }
      }
      else {
        advAlert('You must provide an input key');
      }
    });
  }

  function initSearchPageConfigurator(){
    var mapping = $('#form_search_page #form_mapping').val();
    var def = JSON.parse($('#form_search_page #form_definition').val());
    if(mapping != ''){
      $('#search-page-configurator').detach();
      var container = $('<div id="search-page-configurator"><h2>Configuration</h2><div class="content">Loading</div></div>');
      container.insertBefore($('#form_definition').parent());
      $.ajax({
        url: __ctsearch_base_url + 'search-pages/fields/' + mapping
      }).success(function(data) {
        var fieldSelect = $('<select></select>');
        fieldSelect.append($('<option value="">Select a field</option>'));
        fieldSelect.append($('<option value="_id">_id</option>'));
        for (var i = 0; i < data.length; i++) {
          var field = data[i];
          var option = $('<option></option>');
          option.attr('value', field);
          option.html(field);
          fieldSelect.append(option);
        }

        container.find('.content').html('');

        var analyzer = $('<div class="form-item required"><label for="sp-def-analyzer">Analyzer</label><input type="text" id="sp-def-analyzer" required="required" /></div>');
        container.find('.content').append(analyzer);
        if (typeof def.analyzer !== 'undefined') {
          $('#sp-def-analyzer').val(def.analyzer);
        }

        var size = $('<div class="form-item required"><label for="sp-def-size">Size</label><input type="text" id="sp-def-size" required="required" /></div>');
        container.find('.content').append(size);
        if (typeof def.size !== 'undefined') {
          $('#sp-def-size').val(def.size);
        }

        var facets = $('<div id="sp-def-facets"></div>');
        facets.append($('<h3>Facets</h3>'));
        container.find('.content').append(facets);
        if (typeof def.facets !== 'undefined') {
          for (var i = 0; i < def.facets.length; i++) {
            var facet_name = '';
            var rnd = Math.floor(Math.random() * 1000);
            for (var k in def.facets[i]) {
              facet_name = k;
            }
            var facet_option_container = $('<div class="facet-option sortable-option"></div>');

            var select = fieldSelect.clone();
            select.attr('id', 'facet-field-' + rnd);
            select.find('option').each(function () {
              if ($(this).attr('value') == facet_name) {
                $(this).attr('selected', 'selected');
              }
            });
            facet_option_container.append(select);
            $('<label for="facet-field-' + rnd + '">Field</label>').insertBefore(select);

            var label = $('<input type="text" id="facet-label-' + rnd + '" />');
            if(typeof def.facets[i][facet_name].label !== 'undefined')
              label.val(def.facets[i][facet_name].label);
            facet_option_container.append(label);
            $('<label for="facet-label-' + rnd + '">Facet label</label>').insertBefore(label);

            var stickyLbl = $('<label for="facet-sticky-' + rnd + '">Sticky</label>');
            var stickyChb = $('<input type="checkbox" id="facet-sticky-' + rnd + '" class="sticky-facet" />');
            if(typeof def.facets[i][facet_name].sticky !== 'undefined' && def.facets[i][facet_name].sticky){
              stickyChb.attr('checked', 'checked');
            }
            facet_option_container.append(stickyLbl);
            facet_option_container.append(stickyChb);

            var isDateLbl = $('<label for="facet-isdate-' + rnd + '">Is date?</label>');
            var isDateChb = $('<input type="checkbox" id="facet-isdate-' + rnd + '" class="isdate-facet" />');
            if(typeof def.facets[i][facet_name].isDate !== 'undefined' && def.facets[i][facet_name].isDate){
              isDateChb.attr('checked', 'checked');
            }
            facet_option_container.append(isDateLbl);
            facet_option_container.append(isDateChb);

            var up = $('<a href="#" class="up">Move up</a>');
            var down = $('<a href="#" class="down">Move down</a>');
            var remove = $('<a href="#" class="remove">Remove</a>');
            facet_option_container.append(up);
            facet_option_container.append(down);
            facet_option_container.append(remove);

            facets.append(facet_option_container);
          }
        }
        facets.append($('<div class="action"><a href="#" class="add">Add facet</a></div>'));

        var sorting = $('<div id="sp-def-sorting"></div>');
        sorting.append($('<h3>Sorting</h3>'));
        container.find('.content').append(sorting);
        var defaultSorting = $('<div class="form-item required"><label for="sp-def-default-sorting">Empty search sorting field:</label></div>');
        var defaultSortingSelect = fieldSelect.clone();
        defaultSortingSelect.find('option[value="_id"]').detach();
        defaultSortingSelect.append($('<option value="_score">_score</option>'));
        defaultSortingSelect.attr('id', 'sp-def-default-sorting');
        defaultSortingSelect.attr('required', 'required');
        defaultSorting.append(defaultSortingSelect);
        sorting.append(defaultSorting);
        if (typeof def.sorting !== 'undefined') {
          $('#sp-def-default-sorting').val(def.sorting.default.field);
        }
        var defaultSortingOrder = $('<div class="form-item required"><label for="sp-def-default-sorting-order">Empty search sorting order:</label><select id="sp-def-default-sorting-order" required="required"><option value="">Select</option><option value="asc">asc</option><option value="desc">desc</option></select></div>');
        sorting.append(defaultSortingOrder);
        if (typeof def.sorting !== 'undefined') {
          $('#sp-def-default-sorting-order').val(def.sorting.default.order);
        }
        if (typeof def.sorting !== 'undefined' && typeof def.sorting.fields !== 'undefined') {
          for (var i = 0; i < def.sorting.fields.length; i++) {
            var field = '';
            for (var k in def.sorting.fields[i]) {
              field = k;
            }
            var sorting_option_container = $('<div class="sorting-option sortable-option"></div>');
            var rnd = Math.floor(Math.random() * 1000);

            var sortingFieldSelect = fieldSelect.clone();
            sortingFieldSelect.attr('id', 'sorting-field-' + rnd);
            sortingFieldSelect.find('option[value="_id"]').detach();
            $('<option value="_score">_score</option>').insertAfter(sortingFieldSelect.find('option').first());
            sortingFieldSelect.find('option').each(function () {
              if ($(this).attr('value') == field) {
                $(this).attr('selected', 'selected');
              }
            });
            sorting_option_container.append(sortingFieldSelect);
            $('<label for="sorting-field-' + rnd + '">Field</label>').insertBefore(sortingFieldSelect);

            var label = $('<input type="text" id="sorting-label-' + rnd + '" />');
            label.val(def.sorting.fields[i][field]);
            sorting_option_container.append(label);
            $('<label for="sorting-label-' + rnd + '">Sorting option label</label>').insertBefore(label);

            var up = $('<a href="#" class="up">Move up</a>');
            var down = $('<a href="#" class="down">Move down</a>');
            var remove = $('<a href="#" class="remove">Remove</a>');
            sorting_option_container.append(up);
            sorting_option_container.append(down);
            sorting_option_container.append(remove);

            sorting.append(sorting_option_container);
          }
        }
        sorting.append($('<div class="action"><a href="#" class="add">Add sorting option</a></div>'));

        var results = $('<div id="sp-def-results"></div>');
        results.append($('<h3>Results</h3>'));
        container.find('.content').append(results);
        var results_mapping = ['title', 'thumbnail', 'url', 'excerp'];
        for(var i = 0; i < results_mapping.length; i++){
          var result_mapping_container = $('<div class="result-mapping"></div>');

          $('<span class="result-mapping-field">Result field <strong>' + results_mapping[i] + '</strong> :</span>').appendTo(result_mapping_container);

          var select = fieldSelect.clone();
          select.find('option').each(function(){
            if(typeof def.results !== 'undefined' && results_mapping[i] in def.results && def.results[results_mapping[i]] == $(this).attr('value')){
              $(this).attr('selected', 'selected');
            }
          });
          select.attr('id', 'sp-def-res-' + results_mapping[i]);
          result_mapping_container.append(select);
          $('<label for="">Field</label>').insertBefore(select);

          results.append(result_mapping_container);
        }

        var suggest_container = $('<div id="suggest-container"></div>');
        var multiSelectLabel = $('<label for="">Fields for suggestions:</label>');
        suggest_container.append(multiSelectLabel);
        var multiSelect = fieldSelect.clone();
        multiSelect.attr('multiple', 'multiple');
        multiSelect.find('option').eq(0).detach();
        suggest_container.append(multiSelect);
        results.append(suggest_container);
        if (typeof def.suggest !== 'undefined') {
          multiSelect.find('option').each(function(){
            var selected = false;
            for(var i = 0; i < def.suggest.length; i++){
              if(def.suggest[i] == $(this).attr('value')){
                selected = true;
              }
            }
            if(selected){
              $(this).attr('selected', 'selected');
            }
          });
        }

        var mlt_container = $('<div id="mlt-container"></div>');
        var mltMultiSelectLabel = $('<label for="">Fields for "More like this" feature:</label>');
        mlt_container.append(mltMultiSelectLabel);
        var mltMultiSelect = fieldSelect.clone();
        mltMultiSelect.attr('multiple', 'multiple');
        mltMultiSelect.find('option').eq(0).detach();
        mlt_container.append(mltMultiSelect);
        results.append(mlt_container);
        if (typeof def.more_like_this !== 'undefined') {
          mltMultiSelect.find('option').each(function(){
            var selected = false;
            for(var i = 0; i < def.more_like_this.length; i++){
              if(def.more_like_this[i] == $(this).attr('value')){
                selected = true;
              }
            }
            if(selected){
              $(this).attr('selected', 'selected');
            }
          });
        }

        var autocomplete = $('<div id="sp-def-autocomplete"></div>');
        autocomplete.append($('<h3>Autocomplete</h3>'));
        container.find('.content').append(autocomplete);
        var autoCompleteField = $('<div class="form-item"><label for="sp-def-autocomplete-field">Autocomplete field:</label></div>');
        var autoCompleteFieldSelect = fieldSelect.clone();
        autoCompleteFieldSelect.find('option[value="_id"]').detach();
        autoCompleteFieldSelect.attr('id', 'sp-def-autocomplete-field');
        autoCompleteField.append(autoCompleteFieldSelect);
        autocomplete.append(autoCompleteField);
        var autoCompleteGroupField = $('<div class="form-item"><label for="sp-def-autocomplete-group-field">Autocomplete group field:</label></div>');
        var autoCompleteGroupSelect = fieldSelect.clone();
        autoCompleteGroupSelect.find('option[value="_id"]').detach();
        autoCompleteGroupSelect.attr('id', 'sp-def-autocomplete-group-field');
        autoCompleteGroupField.append(autoCompleteGroupSelect);
        autocomplete.append(autoCompleteGroupField);
        if(typeof def.autocomplete !== 'undefined'){
          $('#sp-def-autocomplete-field').val(def.autocomplete.field);
          $('#sp-def-autocomplete-group-field').val(def.autocomplete.group);
        }


        bindEventsOnSearchPageConfigurator();

        $('#sp-def-facets a.add').click(function(e){
          e.preventDefault();
          handleSearchPageConfigurationAddFieldOption($(this).parent(), fieldSelect, 'facet-option', 'Field', 'Facet label');
        });

        $('#sp-def-sorting a.add').click(function(e){
          e.preventDefault();
          var sortingFieldSelect = fieldSelect.clone();
          sortingFieldSelect.find('option[value="_id"]').detach();
          $('<option value="_score">_score</option>').insertAfter(sortingFieldSelect.find('option').first());
          handleSearchPageConfigurationAddFieldOption($(this).parent(), sortingFieldSelect, 'sorting-option', 'Field', 'Sorting option label');
        });
      });
    }
    if($('#search-page-json-toggle-container').size() == 0) {
      $('<div id="search-page-json-toggle-container"><a href="javascript:void(0)" id="search-page-json-toggle" class="json-link">' + __ctsearch_js_translations.ShowHideJSONDef + '</a></div>').insertBefore($('#form_definition'));
      $('#form_definition').css('display', 'none');
      $('label[for="form_definition"]').css('display', 'none');
      $('#search-page-json-toggle').click(function () {
        $('#form_definition').slideToggle();
      });
    }
  }

  function handleSearchPageConfigurationAddFieldOption(target, fieldSelect, containerClass, fieldLabel, fieldLabelLabel){
    var container = $('<div class="' + containerClass + ' sortable-option"></div>');

    var rnd = Math.floor(Math.random() * 1000);

    var select = fieldSelect.clone();
    select.attr('id', 'sortable-field-' + rnd);
    container.append(select);
    $('<label for="sortable-field-' + rnd + '">' + fieldLabel + '</label>').insertBefore(select);

    var label = $('<input type="text" id="sortable-label-' + rnd + '" />');
    container.append(label);
    $('<label for="sortable-label-' + rnd + '">' + fieldLabelLabel + '</label>').insertBefore(label);

    if(containerClass == 'facet-option'){
      var stickyLbl = $('<label for="sortable-sticky-' + rnd + '">Sticky</label>');
      var stickyChb = $('<input type="checkbox" id="sortable-sticky-' + rnd + '" />');
      container.append(stickyLbl);
      container.append(stickyChb);

      var isDateLbl = $('<label for="facet-isdate-' + rnd + '">Is date?</label>');
      var isDateChb = $('<input type="checkbox" id="facet-isdate-' + rnd + '" class="isdate-facet" />');
      container.append(isDateLbl);
      container.append(isDateChb);
    }

    var up = $('<a href="#" class="up">Move up</a>');
    var down = $('<a href="#" class="down">Move down</a>');
    var remove = $('<a href="#" class="remove">Remove</a>');
    container.append(up);
    container.append(down);
    container.append(remove);

    container.insertBefore(target);
    bindEventsOnSearchPageConfigurator();
  }

  function bindEventsOnSearchPageConfigurator(){
    $('#search-page-configurator').find('select, input').unbind('change');
    $('#search-page-configurator').find('.sortable-option a').unbind('click');
    $('#search-page-configurator').find('select, input').change(function(){
      var config = getSearchPageConfiguration();
      $('#form_definition').val(JSON.stringify(config));
    });
    $('#search-page-configurator').find('.sortable-option a').click(function(e){
      e.preventDefault();
      if($(this).hasClass('up')){
        var parent = $(this).parents('.sortable-option');
        if(parent.prev('.sortable-option') != null){
          parent.insertBefore(parent.prev('.sortable-option'));
        }
      }
      else if($(this).hasClass('down')){
        var parent = $(this).parents('.sortable-option');
        if(parent.next('.sortable-option') != null){
          parent.insertAfter(parent.next('.sortable-option'));
        }
      }
      else if($(this).hasClass('remove')){
        var parent = $(this).parents('.sortable-option');
        parent.detach();
      }
      var config = getSearchPageConfiguration();
      $('#form_definition').val(JSON.stringify(config));
    });
  }

  function getSearchPageConfiguration(){
    var config = {
      analyzer: $('#sp-def-analyzer').val(),
      size: $('#sp-def-size').val(),
      facets: [],
      sorting: {
        default: {
          field: $('#sp-def-default-sorting').val(),
          order: $('#sp-def-default-sorting-order').val(),
        },
        fields: []
      },
      results: {
        title: $('#sp-def-res-title').val(),
        thumbnail: $('#sp-def-res-thumbnail').val(),
        url: $('#sp-def-res-url').val(),
        excerp: $('#sp-def-res-excerp').val()
      },
      suggest:[],
      more_like_this:[]
    };
    $('#sp-def-facets .facet-option').each(function(){
      if($(this).find('select').val() != '') {
        var obj = {};
        obj[$(this).find('select').val()] = {
          label: $(this).find('input[type="text"]').val(),
          sticky: $(this).find('input[type="checkbox"].sticky-facet').is(':checked'),
          isDate: $(this).find('input[type="checkbox"].isdate-facet').is(':checked')
        };
        config.facets.push(obj);
      }
    });
    $('#sp-def-sorting .sorting-option').each(function(){
      if($(this).find('select').val() != '') {
        var obj = {};
        obj[$(this).find('select').val()] = $(this).find('input').val()
        config.sorting.fields.push(obj);
      }
    });
    config.suggest = $('#suggest-container select').val() != null ? $('#suggest-container select').val() : [];
    config.more_like_this = $('#mlt-container select').val() != null ? $('#mlt-container select').val() : [];
    config.autocomplete = {
      field: $('#sp-def-autocomplete-field').val(),
      group: $('#sp-def-autocomplete-group-field').val()
    };
    return config;
  }


  function advAlert(text) {
    text = text.toString();
    var msg = text.split('\n');
    var html = '<ul class="messages">';
    for (var i = 0; i < msg.length; i++) {
      if (msg[i].trim().length > 0)
        html += '<li>' + msg[i].trim() + '</li>';
    }
    html += '</ul>';
    html = '<div><div class="adv-content">' + html + '</div>';
    html += '<div class="adv-actions"><button>' + __ctsearch_js_translations.OK + '</button></div></div>';
    var dialog = $(html).dialog({
      title: '',
      modal: true,
      minWidth: 300,
      dialogClass: 'adv-alert',
      resizable: false,
      close: function () {
        $(this).dialog('destroy').remove();
      },
      show: {
        effect: "drop",
        direction: 'up',
        duration: 300
      },
      hide: {
        effect: "fadeOut",
        duration: 200
      }
    });
    $('.adv-actions button').click(function () {
      $(dialog).dialog('close');
      return false;
    });
  }

  function advConfirm(text, callback) {
    var msg = text.split('\n');
    var html = '<ul class="messages">';
    for (var i = 0; i < msg.length; i++) {
      if (msg[i].trim().length > 0)
        html += '<li>' + msg[i].trim() + '</li>';
    }
    html += '</ul>';
    html = '<div><div class="adv-content">' + html + '</div>';
    html += '<div class="adv-actions"><button class="ok">' + __ctsearch_js_translations.OK + '</button><button class="cancel">' + __ctsearch_js_translations.Cancel + '</button></div></div>';
    var dialog = $(html).dialog({
      title: '',
      modal: true,
      minWidth: 300,
      dialogClass: 'adv-alert',
      resizable: false,
      close: function () {
        $(this).dialog('destroy').remove();
      },
      show: {
        effect: "drop",
        direction: 'up',
        duration: 300
      },
      hide: {
        effect: "fadeOut",
        duration: 200
      }
    });
    $('.adv-actions button.cancel').click(function () {
      $(dialog).dialog('close');
      return false;
    });
    $('.adv-actions button.ok').click(function () {
      $(dialog).dialog('close');
      callback();
      return false;
    });
  }

})(jQuery);