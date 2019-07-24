<?php
include_once './views/common.php';
class BrowserView extends CommonView {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function browser($p) {
	    echo htmlHead();
        ?>	
        <body ng-app="app">
        <?php $this->echoNavbar($p); ?> 
        <div ng-controller="ctrl" id="scope" style="display:none">
    
        	<?php echo htmlPopup(); ?>
        	<h1 ng-if="title != ''">{{title}}</h1>
        	<div class="browserTable">
        		<div class="browserButtons">
        			<button type="button" class="btn btn-success browserShow" ng-click="showClick()" title="{{txt('SHOW')}}">
        				<span>{{txt('SHOW')}}</span>
        			</button>
        			<button type="button" class="btn btn-primary browserAdd" ng-click="addClick()" title="{{txt('ADD')}}">
        				<span>{{txt('ADD')}}</span>
        			</button>
        			<button type="button" class="btn btn-secondary browserEdit" ng-click="editClick()" title="{{txt('EDIT')}}">
        				<span>{{txt('EDIT')}}</span>
        			</button>
        			<button type="button" class="btn btn-danger browserDelete" ng-click="deleteClick()" title="{{txt('DELETE')}}">
        				<span>{{txt('DELETE')}}</span>
        			</button>
        		</div>

        		<table class="table table-striped table-small table-bordered">
        		<thead  class="thead-dark">
        			<tr><th style="width:20px"></th><th>ID</th><th>Title</th></tr>
        		</thead>
        		<tbody>
        		<tr class="browserItem  tr{{(index % 2)}}" ng-repeat="(index,item) in items"
        			   ng-if="((index >= paginatorPageOffsets[paginatorCurrentPage]) && (index < paginatorPageOffsets[paginatorCurrentPage + 1]))" 
        				id="tr_{{item.id}}">
        				<td><input type="radio" name="selectedItem" value="{{item.id}}" /></td>
        				<td>{{item.id}}</td>
        				<td>{{item.title}}</td>
        		</tr>
        		</tbody>
        		</table>
        		
        		<div class="browserButtons">
        			<button type="button" class="btn btn-success browserShow" ng-click="showClick()" title="{{txt('SHOW')}}">
        				<span>{{txt('SHOW')}}</span>
        			</button>
        			<button type="button" class="btn btn-primary browserAdd" ng-click="addClick()" title="{{txt('ADD')}}">
        				<span>{{txt('ADD')}}</span>
        			</button>
        			<button type="button" class="btn btn-secondary browserEdit" ng-click="editClick()" title="{{txt('EDIT')}}">
        				<span>{{txt('EDIT')}}</span>
        			</button>
        			<button type="button" class="btn btn-danger browserDelete" ng-click="deleteClick()" title="{{txt('DELETE')}}">
        				<span>{{txt('DELETE')}}</span>
        			</button>
        		</div>

        		<div class="browserPaginator">
        			<var class="total">{{txt('TOTAL')}}:{{total}}&nbsp;&nbsp;</var>
        			<div class="buttons">
     					<button type="button" class="btn btn-secondary" id="0"  
        						 ng-if="(paginatorCurrentPage > 1)" ng-click="paginatorClick()">
        						 <span>&lt;&lt;</span>
     					</button> 
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage - 1])}}"  
        						 ng-if="(paginatorCurrentPage > 1)" ng-click="paginatorClick()">
        						 <span>&lt;</span>
     					</button> 
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage - 4])}}"  
        						 ng-if="(paginatorCurrentPage > 5)" ng-click="paginatorClick()">
        						 <span>{{(paginatorCurrentPage - 4)}}</span>
     					</button>  
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage - 3])}}"  
        						 ng-if="(paginatorCurrentPage > 5)" ng-click="paginatorClick()">
        						 <span>{{(paginatorCurrentPage - 3)}}</span>
     					</button>  
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage - 2])}}"  
        						 ng-if="(paginatorCurrentPage > 4)" ng-click="paginatorClick()">
        						 <span>{{(paginatorCurrentPage - 2)}}</span>
     					</button>  
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage - 1])}}"  
        						 ng-if="(paginatorCurrentPage > 3)" ng-click="paginatorClick()">
        						 <span>{{(paginatorCurrentPage - 1)}}</span>
     					</button>  
     					
     					<button type="button" class="btn btn-secondary paginatorCurrent" id="{{(paginatorPageOffsets[paginatorCurrentPage])}}"  
        						 ng-click="paginatorClick()" disabled="disabled" style="cursor:default">
        						 <span>{{paginatorCurrentPage}}</span>
     					</button>  
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage + 1])}}"  
        						 ng-if="(paginatorCurrentPage < (paginatorLastPage - 1))" 
        						 ng-click="paginatorClick()">
        						 <span>{{(paginatorCurrentPage + 1)}}</span>
     					</button>  
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage + 2])}}"  
        						 ng-if="(paginatorCurrentPage < (paginatorLastPage - 2))" ng-click="paginatorClick()">
        						 <span>{{(paginatorCurrentPage + 2)}}</span>
     					</button>  
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage + 3])}}"  
        						 ng-if="((paginatorCurrentPage < (paginatorLastPage - 3)) && (paginatorCurrentPage < 3))" 
        						 ng-click="paginatorClick()">
        						 <span>{{(paginatorCurrentPage + 3)}}</span>
     					</button>  
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage + 4])}}"  
        						 ng-if="((paginatorCurrentPage < (paginatorLastPage - 4)) && (paginatorCurrentPage < 2))" 
        						 ng-click="paginatorClick()">
        						 <span>{{(paginatorCurrentPage + 4)}}</span>
     					</button>  
     					<button type="button" class="btn btn-secondary" id="{{(paginatorPageOffsets[paginatorCurrentPage + 1])}}"  
        						 ng-if="(paginatorCurrentPage < paginatorLastPage)" 
        						 ng-click="paginatorClick()">
        						 <span>&gt;</span>
     					</button> 
     					<button type="button" class="btn btn-secondary" id="{{paginatorPageOffsets[paginatorLastPage]}}"  
        						 ng-if="(paginatorCurrentPage < paginatorLastPage)" 
        						 ng-click="paginatorClick()">
        						 <span>&gt;&gt;</span>
     					</button>  
        			</div><!-- .buttons -->	
        		</div>
        	</div>
        	
        	<p>param1 = {{param1}}</p>
        	<p>errorMsg = {{errorMsg}}</p>
        	<p>avatar = {{avatar}}</p>
        	<p>sid = {{sid}}</p>
			<p>{{txt('YES')}}</p>	
  	  
          <?php loadJavaScriptAngular('browser',$p); ?>
          <?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
}
?>