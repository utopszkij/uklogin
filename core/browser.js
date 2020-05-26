				function titleClick(colName, porder, order_dir) {
					$('#working').show();				
					if (colName == porder) {
						if ((order_dir == 'ASC') | (order_dir == 'asc') | (order_dir == '')) {
							$('#order_dir').val('DESC');
						} else {
							$('#order_dir').val('ASC');
						}	
					} else {
						$('#order_dir').val('ASC');
					}
					$('#order').val(colName);
					$('#offset').val(0);
					$('#browserForm').submit();					
				}
				
				function itemClick(id) {
					$('#working').show();				
					$('#task').val($('#itemTask').val());
					$('#id').val(id);
					$('#browserForm').submit();					
				}
				
				function searchClick() {
					$('#working').show();				
					$('#offset').val(0);
					$('#browserForm').submit();					
				}

				function delSearchClick() {
					$('#working').show();				
					$('#searchstr').val('');
					$('#offset').val(0);
					$('#browserForm').submit();					
				}
				
				function paginatorClick(offset) {
					$('#working').show();				
					$('#offset').val(offset);
					$('#browserForm').submit();					
				}
				
				$(function() {
					$('#working').hide();				
				});			
