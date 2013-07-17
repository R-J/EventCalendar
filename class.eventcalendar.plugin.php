<?php if (!defined('APPLICATION')) exit();

$PluginInfo['EventCalendar'] = array(
  'Name' => 'EventCalendar',
  'Description' => 'Enable the usage of discussions in a certain category as events. ',
   'Version' => '0.01',
   'Author' => "Robin",
   // 'HasLocale' => TRUE
);

/*

# TODO (order shows prio)

## create discussion
1. insert fields for event date and event time
2. save extra info to db
3. change button text from "New Discussion" to "New Event"


## read discussion
1. show extra info (date and time) below <div class="Tabs HeadingTabs EventTabs FirstPage">


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
    $Structure = Gdn::Structure();
    $Structure->Table('EventCalendar_Event')
      ->PrimaryKey('EventID')
      ->Column('DiscussionID', 'int', FALSE)
      ->Column('EventDate', 'date', FALSE)
      ->Column('EventTime', 'varchar(32)', TRUE)
      ->Set(FALSE, FALSE);
  }
  public function PostController_BeforeBodyInput_Handler($Sender) {
    echo '<div class="EventCalendarFields">';
    echo Wrap($Sender->Discussion->CategoryID, 'h1');
    echo $Sender->Form->Label('Event Date', 'Date');
    echo Wrap($Sender->Form->TextBox('Date', array('type' => 'date', 'class' => 'InputBox DateBox'))
      , 'div'
      , array('class' => 'TextBoxWrapper')
    );
    
    echo $Sender->Form->Label('Event Time', 'Time');
    echo Wrap($Sender->Form->TextBox('Time', array('class' => 'InputBox SmallInput'))
      , 'div'
      , array('class' => 'TextBoxWrapper')
    );
    echo '</div>';
  }
  
}
