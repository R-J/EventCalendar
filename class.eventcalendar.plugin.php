<?php if (!defined('APPLICATION')) exit();

// change this manually as long as there is no possibility in the dashboard!
// you can find the number of your categories either in the db itself or in the dashboard, when you edit categories
define('EVENTCALENDAR_CATEGORY', '5');

$PluginInfo['EventCalendar'] = array(
  'Name' => 'EventCalendar',
  'Description' => 'Enable the usage of discussions in a certain category as events. ',
   'Version' => '0.01pre-alpha',
   'Author' => "Robin",
   'HasLocale' => TRUE
);

/*

# TODO (order shows prio)

## create discussion
done 1. insert fields for event date and event time
done 2. save extra info to db
current 3. allow only for category from settings
4. validate input
(X. change button text from "New Discussion" to "New Event")



## read discussion
1. show extra info (date and time) below <div class="Tabs HeadingTabs EventTabs FirstPage">


## event input only in "right" category
1. disable extra event fields per css or javascript if not the event category is chosen
2. check for right category after POST


## create category listings 
1. /categories/events/month/mm/yyyy
2. /categories/events/week/dd/mm/yyyy
3. /categories/events/day/dd/mm/yyyy
4. /categories/events/ sort order should be by event date
5. discussion title prefixed by eventdate: discussiontitle
6. change button text from "New Discussion" to "New Event"


## create modules
1. upcoming X events (X should be configurable)
2. calendar with link to daily view (link for each day with event) and monthly view (month is a link)
<|      month     |>
 1  2  3  4  5  6  7
 8  9 10 11 12 13 14
15 16 17 18 19 20 21
22 23 24 25 26 27 28
29 30 31


## edit discussion
1. show extra fields
2. prefill the wih values from db
3. save to db


## delete discussion
1. delete also from EventCalendar_* tables


## config settings in dashboard. start with setting them manually :-/
1. category number to be treated as event category
  $Configuration['Plugins']['EventCalendar']['EventCategory'] = ???;
2. module upcoming events: how much events to show
  $Configuration['Plugins']['EventCalendar']['UpcomingEventsCount'] = ???;


## possibility for cleanup
OnDisable must show a warning!
one of those in order not to lose data if plugin has to be disabled
a) EventCalendar_* tables will only be deleteted when chosen in a special subsection of dashboard
b) possibility to export and import events

  
# ALREADY DONE!

## structure
1. create table for additional event info (EventCalendar_Event)
  
*/
class EventCalendar implements Gdn_IPlugin {

  public function Setup() {
    // Create new table for additional event info
    $Structure = Gdn::Structure();
    $Structure->Table('EventCalendar_Event')
      ->PrimaryKey('EventID')
      ->Column('DiscussionID', 'int', FALSE)
      ->Column('EventDate', 'date', FALSE)
      ->Column('EventTime', 'varchar(32)', TRUE)
      ->Set(FALSE, FALSE);
    // set config value
    SaveToConfig('Plugins.EventCalendar.EventCategory', EVENTCALENDAR_CATEGORY, array(TRUE, FALSE));
  } // End of Setup
  
  public function PostController_BeforeBodyInput_Handler($Sender) {
 
    echo '<div class="EventCalendarFields">';

    // Input for Event Date
    echo $Sender->Form->Label('Event Date', 'Date');
    echo Wrap($Sender->Form->TextBox('EventDate', array('type' => 'date', 'class' => 'DateBox', 'required' => 'required', 'placeholder' => T('YYYY-MM-DD')))
      , 'div'
      , array('class' => 'TextBoxWrapper')
    );
    
    // Input for Event time
    echo $Sender->Form->Label('Event Time', 'Time');
    echo Wrap($Sender->Form->TextBox('EventTime', array('class' => 'InputBox SmallInput', 'placeholder' => T('e.g. afternoon, 8pm, 13:30,...')))
      , 'div'
      , array('class' => 'TextBoxWrapper')
    );
    echo '</div>';
  } // End of PostController_BeforeBodyInput_Handler
  
  public function PostController_AfterDiscussionSave_Handler($Sender) {
    // AfterDiscussionSave is a bad place to hook, so all error checkings must be done on client side with js :-(
    // Furthermore there is no way to give the user a feedback that insert process hasn't worked
  
    // get CategoryID in order to check if event posting is allowed in discussions category
    $DiscussionID = $Sender->GetJson()['DiscussionID'];
    $CategoryID = $Sender->DiscussionModel->GetID($DiscussionID)->CategoryID;
    if ($CategoryID == C('Plugins.EventCalendar.EventCategory')) {
// debug
// if (TRUE) {
      $EventDate = $Sender->Form->GetFormValue('EventDate', '0000-00-00');
      $EventTime = $Sender->Form->GetFormValue('EventTime', '');

      Gdn::SQL()->Insert('EventCalendar_Event', array(
        'EventDate' => $EventDate
        , 'EventTime' => $EventTime
        , 'DiscussionID' => $DiscussionID
      ));
    }  

// debug
$strdebug = 'catid='.gettype($CategoryID).'!';
Gdn::SQL()
->Update('EventCalendar_Event')
->Set('EventTime', $strdebug)
->Where('EventID', 14)
->Put();

  } // End of PostController_AfterDiscussionSave_Handler
} // End of class EventCalendar
