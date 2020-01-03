<?php
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

include('functions.php');
unset($_SESSION['poll_answered']);
?>

   <div class="container">
       <div class="row">
           <h3 align="center">Simple Polling</h3>
           <div class="col-sm-4 col-sm-offset-4">
               <div class="panel panel-primary">
                   <div class="panel-heading">
                       <h3 class="panel-title">
                           <span class="glyphicon glyphicon-arrow-right"></span><span class="question"></span>  </h3>
                   </div>
                       <div class="panel-body">
                          <div class="col-sm-12"> <div class="poll">Loading...</div></div>
                          <div class="poll-content">
                             <ul class="list-group">

                             </ul>
                          </div>
                       </div>

                       <div class="panel-footer">
                           <div class="row"><div class="col-sm-6"> <a href="#"></a> <button type="button" class="btn btn-primary btn-sm button">
                                       Vote</button></a></div> <div class="col-sm-6"><a href="javascript:;" onClick="javascript:get_poll();"><div class="btn btn-primary btn-sm pull-right selesai"> Next Question</div></a></div></div>
                   </div>
    	         </div>
          </div>
      </div>
  </div>
