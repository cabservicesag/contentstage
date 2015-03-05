/**
 * Anonymous and self-invoking Compare function.
 * Working in JS strict mode.
 *
 * @param {jQuery} $ jQuery anonymous instance
 * @returns {undefined}
 */
;"use strict";
(function($){
	/**
	 * Start on document.ready
	 * 
	 * @returns {Function}
	 */
	$(function(){
		/**
		 * Defines if debug messages in console using this.cdbg(...) are enabled
		 */
		this.enableDebug = true;
		
		/**
		 * Debug message to console with lead tag
		 * 
		 * @param {type} msg The message
		 * @param {type} context (Optional) The context message
		 * @returns {undefined}
		 */
		this.cdbg = function(msg, context) {
			var CLEAD = '[CompareJS]';
			
			if (this.enableDebug) {
				if (context) {
					console.log(CLEAD, context, msg);
				} else {
					console.log(CLEAD, msg);
				}
			}
		};
		
		/**
		 * Recursive function to generate the tree construct
		 * 
		 * @param {Object} treeNode A tree node
		 * @param {Object} parentNode The parent node of the current domNode
		 * @param {Boolean} isRoot Defines that this node is the root
		 * @returns {undefined}
		 */
		this.walkTree = function(treeNode, parentNode, isRoot) {
			// Determine the current target node for this treeNode
			var targetNode = $('<div />').addClass('compare-box');
			
			// Append only to non-root nodes the collapsed class
			if (!isRoot) {
				$(targetNode).css('display', 'none');
			}
			
			// Render current treeNode
			var pageChanges = this.getPageChanges(treeNode, true);
			
			if (pageChanges.anyChanges()) {
				this.renderNode(treeNode, targetNode, parentNode, pageChanges);
			}
			
			// Find children and provide the current target Node as parent Node
			if (treeNode._children) {
				// Iterate over children and apply this function recursivly
				for (var cCnt = 0; cCnt < treeNode._children.length; cCnt++) {
					this.walkTree(treeNode._children[cCnt], targetNode, false);
				}
			}
		};
		
		/**
		 * Renders the given tree node to given destination DOM Node
		 * 
		 * @param {Object} treeNode Tree node to render
		 * @param {Object} targetNode Target DOM Node to render to
		 * @param {Object} parentNode The parent DOM Node of targetNode - the
		 * @param {Object} pageChanges Contains informations about changes in
		 * current treeNode
		 * render target.
		 * @returns {undefined}
		 */
		this.renderNode = function(treeNode, targetNode, parentNode, pageChanges) {
			// Append heading
			var heading = $('<strong />')
								.addClass('compare-page-title')
								.addClass('t3-row-header')
								.text(treeNode.title)
								.click(this.toggleNodeEvent);
			
			// Check if this node has children with changes and show count in
			// header
			if (treeNode._children) {
				var childrenWithChanges = 0;
				
				for (var cCnt = 0; cCnt < treeNode._children.length; cCnt++) {
					if (this.getPageChanges(treeNode._children[cCnt], false).anyChanges()) {
						childrenWithChanges++;
					}
				}
				
				$(heading).text($(heading).text() + ' (' + childrenWithChanges + ')');
			}
			
			// If there are changes in root level, set class for root level
			// changes
			if (pageChanges.rootLevel) {
				$('<span />')
						.addClass('changes-rootlevel')
						.insertAfter(heading);
			}
			// If there are changes in recursive level, set class for recursive
			// level changes
			else if (pageChanges.recursiveLevel) {
				$('<span />')
						.addClass('changes-recursivelevel')
						.insertAfter(heading);
			}
			
			// Get differences for current node
			var differences = this.getDifferencesForPage(treeNode.uid);
			
			// Iterate over differences, if there are any differences
			if (differences !== false) {
				// Generate diff table
				var cmpTable = $('<table />')
						.addClass('compare-table')
						.appendTo(targetNode);
				
				// Write table heading
				$('<thead />')
						.append('<tr />', [
							$('<th />').text('Feld'),
							$('<th />').text('Lokaler Stand'),
							$('<th />').text('Remote Stand')
						]).appendTo(cmpTable);
				
				// Write table body
				var cmpTableBody = $('<tbody />').appendTo(cmpTable);
				
				// Iterate over differences, get the difference Object, which
				// uses the tcaKey as key
				for (var tcaKey in differences) {
					var records = differences[tcaKey];
					
					// Iterate over different records in current table
					for (var record in records) {
						var fields = records[record];
						
						// Iterate over fields of current different record
						for (var field in fields) {
							var fieldNameLL = this.getFieldName(tcaKey, field);
							var diffJQ = $(fields[field]);
							var diffRed = $(diffJQ).closest('span.diff-r');
							var diffGreen = $(diffJQ).closest('span.diff-g');
							
							// Start new row for current field
							$('<tr />')
									.append([
										$('<td />').text(fieldNameLL),
										$('<td />').append([diffRed]),
										$('<td />').append([diffGreen])
									]).appendTo(cmpTableBody);
							
						} // END Field Iterator
					
					} // END Record Iterator
				
				} // END Table Iterator
			}
			
			// Render everything to parentNode
			$(parentNode)
					.append(heading)
					.append(targetNode);
		};
		
		/**
		 * Returns the differences Object for given page UID
		 * 
		 * @param {Integer} pageUid The UID of the data record
		 * @returns {Object|Boolean} The Object with differences or false, if
		 * there is no entry in differences for the specified UID.
		 */
		this.getDifferencesForPage = function(pageUid) {
			return $.contentstage.differences[pageUid] || false;
		};
		
		/**
		 * Gets the table configuration from TCA.
		 * 
		 * @param {String} tcaKey The TCA key for the table
		 * @returns {Object|Boolean} The Object with the table configuration or
		 * false, if the given table was not found in TCA
		 */
		this.getTableConfiguration = function(tcaKey) {
			return $.contentstage.TCA[tcaKey] || false;
		};
		
		/**
		 * Gets the configuration for a specific field from a specific table
		 * configuration from TCA.
		 * 
		 * @param {String} tcaKey The TCA key of the table which holds the field
		 * @param {String} fieldName The name of the field
		 * @returns {Object|Boolean} The Object with the field configuration or
		 * false, if the given field was not found in the table configuration.
		 */
		this.getFieldConfiguration = function(tcaKey, fieldName) {
			var tableConf = this.getTableConfiguration(tcaKey);
			
			if (tableConf !== false) {
				return tableConf[fieldName] || false;
			} else {
				return false;
			}
		};
		
		/**
		 * Gets the translated name for a specific field from a specific table
		 * configuration from TCA.
		 * 
		 * @param {String} tcaKey The TCA key of the table which holds the field
		 * @param {String} fieldName The name of the field
		 * @returns {String|Boolean}
		 */
		this.getFieldName = function(tcaKey, fieldName) {
			return this.getFieldConfiguration(tcaKey, fieldName)['__name'] || false;
		};
		
		/**
		 * Checks if the given page tree Node has any changes inside
		 * 
		 * @param {String} treeNode The treeNode to examine
		 * @param {String} recursive Set to true if you want to include all child
		 * nodes
		 * @returns {Object} An Object containing informations if the given
		 * treeNode has changes in rootLevel and recursiveLevel. There is also
		 * a function called anyChanges() you can call to check if there are
		 * any changes in root or recursive level.
		 */
		this.getPageChanges = function(treeNode, recursive) {
			// This object hols information about changes for given treeNode
			var changes = {
				rootLevel: false,
				recursiveLevel: false,
				
				anyChanges: function() {
					return (changes.rootLevel || changes.recursiveLevel);
				}
			};
			
			// First check, if there are any differences for current page
			if ($.contentstage.differences[treeNode.uid]) {
				changes.rootLevel = true;
			}
			
			// If recursive examination is enabled and the current tree Node has
			// children, search for changes in all following nodes
			if (recursive && treeNode._children) {
				for (var cCnt = 0; cCnt < treeNode._children.length; cCnt++) {
					var recChanges = this.getPageChanges(treeNode._children[cCnt], recursive);
					if (recChanges.rootLevel) {
						changes.recursiveLevel = true;
					}
				}
			}
			
			// Return object of changes
			return changes;
		};
		
		/**
		 * Event to toggle a node
		 * 
		 * @returns {jQuery}
		 */
		this.toggleNodeEvent = function() {
			return $(this)
					.next('.compare-box')
					.stop()
					.slideToggle(250);
		};
		
		/**
		 * Initializes CompareJS
		 * 
		 * @returns {undefined}
		 */
		this.boot = function() {
			// Debug output
			this.cdbg('Booting CompareJS');
			this.cdbg($.contentstage);
			
			// Generate PageTree
			var startNode = $('#compare-container');
			this.walkTree($.contentstage.pageTree, startNode, true);
		};
		
		// Startup CompareJS
		this.boot();
	});
})(jQuery);