jQuery(function($){
	var differenceContainer = $('.differencesContainer .differences'), tcaLabel = function (table, field) {
		if (!table || !$.contentstage.TCA || !$.contentstage.TCA[table]) return table;
		if (!field) {
			return $.contentstage.TCA[table]['__name'] ? $.contentstage.TCA[table]['__name'] : table;
		}
		if (!$.contentstage.TCA[table][field]) return field;
		return $.contentstage.TCA[table][field]['__name'] ? $.contentstage.TCA[table][field]['__name'] : field;
	}, renderPageDifferences = function (page) {
		var tables = $.contentstage.differences[page]; //, dom = $('<li class="page" data-page="' + page + '"/>'), ul = $('<ul class="tableContainer" />').appendTo(dom);
		if (!tables) return [];
		var changes = [];
		
		$.each(tables, function (table, records) {
			$.each(records, function (record, fields) {
				$.each(fields, function (field, change) {
					changes.push({
						page: page,
						table: table,
						record: record,
						field: field,
						tableName: tcaLabel(table),
						fieldName: tcaLabel(table, field),
						change: change
					});
				});
			});
		});
		
		return changes;
	}, iLightboxOptions = {closeKeys: [27]};
	
	//$('#contentstagePageTreeTemplate').tmpl($.contentstage.pageTree).appendTo($('ul.pageTree'));
	
	$('ul.pageTree').treeview({collapsed: true}).find('a.pageNode').click(function(){
		differenceContainer.empty();
		
		var th = $(this), pid = th.attr('data-page'), pages = [];
		th.parent().find('a.pageNode').each(function () {
			var pid = $(this).attr('data-page');
			pages.push({page: pid, changes: renderPageDifferences(pid), title: $(this).html()});
		});
		
		$('#contentstagePageChangesTemplate').tmpl(pages).appendTo(differenceContainer);
		
		differenceContainer.find('table.typo3-dblist tr.infoRow').each(function(){
			var $row = $(this);
			$row.find('.t3-icon-document-open').click(function(){
				var width = 0.9 * $(window).width(),
					height = 0.9 * $(window).height();
				$('<iframe src="/typo3/alt_doc.php?edit[' + $row.data('table') + '][' + $row.data('record') + ']=edit&noView=0&returnUrl=/typo3conf/ext/contentstage/Resources/Public/HTML/Return.html" width="' + width + '" height="' + height + '" scrolling="no" border="no" frameBorder="0" style="width:' + width + 'px;height:' + height + 'px;"/>').iLightbox(iLightboxOptions);
			});
		});
		
		return false;
	});
	
	$('ul.pageTree').find('strong.changes').parents('li').children('a.pageNode').addClass('subHasChanges');
	$('ul.pageTree').find('.contentstage-action-changes').parents('li').children('a.pageNode').addClass('subHasChanges');
	
	$('.sliderWrap').each(function(){
		var $wrap = $(this),
			$click = $wrap.find('.sliderClick'),
			$content = $wrap.find('.sliderContent');
		$click.click(function(){
			$content.slideToggle('fast', function(){
				$wrap.toggleClass('open');
			});
		});
	});
	
	$('.submitContainer').each(function(){
		var $container = $(this);
		$container.find('span').click(function(){
			$container.find('input[type="submit"]').click();
		});
	});
	
	$('form.iLightbox').each(function(){
		var $form = $(this),
			$content = $form.find('.iLightboxContent'),
			calledFromLightbox = false;
		$(document).on('iLightboxClose', function(){
			calledFromLightbox = false;
		});
		$form.submit(function(){
			if (!calledFromLightbox) {
				
				$content.iLightbox(iLightboxOptions).wrap('<form />').wrap('<div class="tx_contentstage" />')
					.closest('form')
					.submit(function(){
						calledFromLightbox = true;
						$.each($(this).serializeArray(), function(i, v){
							$('<input type="hidden" name="' + v['name'] + '" value="' + v['value'] + '" />').appendTo($form);
						});
						$form.submit();
						return false;
					});
				
				return false;
			}
		});
	});
	
	$('.reviewedForm').each(function(){
		var $th = $(this);
		$th.find('input[type="submit"]').click(function(){
			$th.find('.hiddenSubmit').val($(this).val());
		});
	});
	
	$('div.reviews .reviewRow').each(function(){
		var $row = $(this);
		$row.find('td').click(function(e){
			if (e.target.nodeName.toLowerCase() === 'a') return true;
			
			document.location = $row.find('a.compare').attr('href');
		});
	});
});
