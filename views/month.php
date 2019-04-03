<?php defined('APPLICATION') or die;

$userPhotoFirst = Gdn::config('Vanilla.Comment.UserPhotoFirst', true);

$dayLink = $this->canonicalUrl().'/'.$this->data('Year').'/'.$this->data('Month').'/';
$monthFirst = $this->data('MonthFirst');
$monthLast = $this->data('MonthLast');

$events = $this->data('Events');
if (count($events) < 1) {
    echo '<p>'.t('No events yet').'</p>';
    return;
}

$jsEvents = array();
$i = 0;
//var_dump($events);
foreach($events as $event) {
	$user = Gdn::userModel()->getID($event['UserID']);
	$color =  "#".Gdn::UserMetaModel()->GetUserMeta($event['UserID'], 'Profile.CouleurAgenda')['Profile.CouleurAgenda'];
	//var_dump($user->Name);
	$jsEvents[$i]['user'] = $user->Name;
	$jsEvents[$i]['userID'] = $event['UserID'];
	$jsEvents[$i]['DiscussionID'] = $event['DiscussionID'];
	$jsEvents[$i]['title'] = $event['Name'];
	$jsEvents[$i]['start'] = $event['EventCalendarDate'];
	$jsEvents[$i]['startDate'] = strftime(t('EventCalendar.DateFormat', '%A, %e %B %Y'), strtotime($event['EventCalendarDate']));
	$jsEvents[$i]['link'] =  anchor(t('Voir la discussion'), '/discussion/'.$event['DiscussionID'], 'Button');
	$jsEvents[$i]['body'] =  Gdn_Format::to($event['Body'], $event['Format']);
	if( !is_null($color) ) {
		$jsEvents[$i]['color'] = $color;
	}
	if( !is_null($event['EventCalendarDateEnd']) ) { 
		$jsEvents[$i]['end'] = $event['EventCalendarDateEnd']."T23:59:00";
		$jsEvents[$i]['endDate'] = strftime(t('EventCalendar.DateFormat', '%A, %e %B %Y'), strtotime($event['EventCalendarDateEnd']));
	}
	$i++;
}
$jsEvents = json_encode($jsEvents);
?>

	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <link href='/js/fullcalendar/packages/core/main.css' rel='stylesheet' />
    <link href='/js/fullcalendar/packages/daygrid/main.css' rel='stylesheet' />
	<link href='/js/fullcalendar/packages/bootstrap/main.css' rel='stylesheet' />
	<link href='/js/fullcalendar/bootstrap.min.css' rel='stylesheet' />
	
    <script src='/js/fullcalendar/packages/core/main.js'></script>
	<script src='/js/fullcalendar/packages/core/locales/fr.js'></script>
    <script src='/js/fullcalendar/packages/daygrid/main.js'></script>
	<script src='/js/fullcalendar/packages/bootstrap/main.js'></script>

    <script>

      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
		var el;
        var calendar = new FullCalendar.Calendar(calendarEl, {
			plugins: [ 'dayGrid', 'bootstrap' ],
			locale: 'fr',
			editable: false,
			height: 'auto',
			handleWindowResize: true,
			displayEventTime: false,
			defaultView: 'dayGridMonth',
			eventClick: function(info) {
				//console.log(info.event);
				//console.log(info.event.extendedProps.endDate);
				$(".fc-day-grid-event").popover('hide');
				if( typeof(info.event.extendedProps.endDate) !== 'undefined' ) {
					var dateFormate =  " du " + info.event.extendedProps.startDate + " au " + info.event.extendedProps.endDate;
				} else {
					var dateFormate =  " le " + info.event.extendedProps.startDate
				}
				
				$(info.el).popover({
						title: info.event.title + " -- " + dateFormate,
						content: 'Evénement crée par <a href="/profile/'+info.event.extendedProps.user+'">'+info.event.extendedProps.user+'</a><br />' + info.event.extendedProps.body + '<br />' + info.event.extendedProps.link,
						html: true
				}).popover('show');
				; 
			},
			events: <?php echo $jsEvents; ?>
        });

        calendar.render();
		
		$("#closeAll").on('click', function(e) {
			e.preventDefault();
			$(".fc-day-grid-event").popover('hide');
		});
      });

    </script>
	
	<div id='calendar'></div>
	
	<a href="#" id="closeAll" class="btn btn-secondary">Fermer les popups</a>
	<style type="text/css">
.fc-day-grid-event .fc-content {
    white-space: nowrap;
    overflow: hidden;
    height: 20px;
    line-height: 20px;
    font-size: 14px;
}
.popover-body {

    padding: 0.5rem 0.75rem;
    color: #444;
    max-height: 500px;
    overflow: auto;

}
	</style>
