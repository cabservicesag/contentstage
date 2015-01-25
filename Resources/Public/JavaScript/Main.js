jQuery(function($){
	var differenceContainer = $('.differencesContainer ul.differences'), tcaLabel = function (table, field) {
		if (!table || !$.contentstage.TCA || !$.contentstage.TCA[table]) return table;
		if (!field) {
			return $.contentstage.TCA[table]['__name'] ? $.contentstage.TCA[table]['__name'] : table;
		}
		if (!$.contentstage.TCA[table][field]) return field;
		return $.contentstage.TCA[table][field]['__name'] ? $.contentstage.TCA[table][field]['__name'] : field;
	}, renderPageDifferences = function (page) {
		var tables = $.contentstage.differences[page], dom = $('<li class="page" data-page="' + page + '"/>'), ul = $('<ul class="tableContainer" />').appendTo(dom);
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
	};
	
	//$('#contentstagePageTreeTemplate').tmpl($.contentstage.pageTree).appendTo($('ul.pageTree'));
	
	$('ul.pageTree').treeview({collapsed: true}).find('a').click(function(){
		differenceContainer.empty();
		
		var th = $(this), pid = th.attr('data-page'), pages = [];
		th.parent().find('a').each(function () {
			var pid = $(this).attr('data-page');
			pages.push({page: pid, changes: renderPageDifferences(pid), title: $(this).html()});
		});
		
		$('#contentstagePageChangesTemplate').tmpl(pages).appendTo(differenceContainer);
		
		return false;
	});
	
	$('ul.pageTree').find('strong.changes').parents('li').children('a').addClass('subHasChanges');
});
