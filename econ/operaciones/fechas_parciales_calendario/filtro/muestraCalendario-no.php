<?php
?>
<!-- <meta charset='UTF-8' /> -->
<link href='css/fullcalendar.css' rel='stylesheet' />
<script src='js/moment.min.js'></script>
<script src='js/jquery.min.js'></script>
<script src='js/fullcalendar.min.js'></script>
<script src='js/lang-all.js'></script>
<!-- <script src='js/fullcalendar.load.js'></script> -->
<script>
var today = new Date();
var currentDate = today.getDate();

$(document).ready(function() {
		
		$('#calendar').fullCalendar({
			lang: 'es',
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,basicWeek,basicDay'
			},
			editable: true,
			eventLimit: true, // allow "more" link when too many events
			selectable: true,
                        eventSources: [
					'./getdatos.php'					
				],
                        eventRender: function(event, element) {
                            element.attr('title', event.tip);
                        }
                });
	});

	$('#calendar').fullCalendar('gotoDate', currentDate);
</script>

<style>
	#contenedor_calendario {
		margin: 5px 5px;
		margin-top: 10px;
		padding: 0;
		padding-top: 10px;
		padding-bottom: 10px;
		font-size: 14px;
		background-color: white;
	}

	#calendar {
		max-width: 700px;
		margin: 0 auto;
	}

</style>


<div id='contenedor_calendario'>
	<div id='calendar'></div>
</div>

