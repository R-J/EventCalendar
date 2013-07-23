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
done 1. show extra info (date and time) below <div class="Tabs HeadingTabs EventTabs FirstPage">


## event input only in "right" category
1. disable extra event fields per css or javascript if not the event category is chosen
current 2. check for right category after POST


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
done 1. show extra fields
done 2. prefill the wih values from db
done 3. save to db


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
    // create table and init config values
    public function Setup() {
        // Create new table for additional event info
        $Structure = Gdn::Structure();
        $Structure->Table('EventCalendar_Event')
            ->PrimaryKey('EventID')
            ->Column('DiscussionID', 'int', FALSE)
            ->Column('EventDate', 'date', FALSE)
            ->Column('EventTime', 'varchar(32)', TRUE)
            ->Column('EventDebug', 'varchar(320)', TRUE)
            ->Set(FALSE, FALSE);
        // set config value
      SaveToConfig('Plugins.EventCalendar.EventCategory', EVENTCALENDAR_CATEGORY, array(TRUE, FALSE));
    } // End of Setup

    // add css
    public function DiscussionController_BeforeDiscussionRender_Handler($Sender) {
        $Sender->AddCssFile('custom.css', 'plugins/EventCalendar/design');
    } // End of DiscussionController_BeforeDiscussionRender_Handler

    // Show Event information in discussion
    public function DiscussionController_BeforeCommentBody_Handler($Sender) {
        // only show in discussion, not in comments!
        if ($Sender->EventArguments['Type'] != 'Discussion') {
            return;
        }

        $DiscussionID = $Sender->EventArguments['Object']->DiscussionID;
        $EventCalendar_Event = Gdn::SQL()->Select('*')
            ->From('EventCalendar_Event')
            ->Where('DiscussionID', $DiscussionID)
            ->Get()
            ->FirstRow();
        echo '<div id="EventCalendar_Container">';
        echo Wrap('Event Date: '.date(T('d.m.Y'), strtotime($EventCalendar_Event->EventDate).'T00:00:00'),'div', array('class' => 'EventDate'));
        echo Wrap('Event Time: '.$EventCalendar_Event->EventTime, 'div', array('class' => 'EventTime'));
        echo '</div>';

    } // End of DiscussionController_AfterCommentMeta_Handler
    
    // Add Form Fields to "New Discussion" and "Edit Discussion" (it's the same!) 
    public function PostController_BeforeBodyInput_Handler($Sender) {
        echo '<div class="EventCalendarFields">';

        // Input for Event Date
        echo $Sender->Form->Label('Event Date', 'Date');
        echo Wrap($Sender->Form->TextBox('EventDate', array('type' => 'date', 'class' => 'DateBox', 'required' => 'required', 'placeholder' => date(T('d.m.Y'), getdate())))
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

    // join eventcalendar tables to show them automagically in edit discussion
    public function DiscussionModel_BeforeGet_Handler($Sender) {
        if (C('Plugins.EventCalendar.Enabled')) { 
            $Sender->SQL->Select('ec.EventTime, ec.EventDate')
                ->Join('EventCalendar_Event ec', 'ec.DiscussionID = d.DiscussionID', 'left');
        }
    } // End of DiscussionModel_BeforeGet_Handler

    // Validate inputs
    public function DiscussionModel_BeforeSaveDiscussion_Handler($Sender) {
        // und categoryid <> config und vielleicht noch controller = discussioncontroller?
        if (!C('Plugins.EventCalendar.Enabled')) {
            return;
        }

        $FormPostValues = GetValue('FormPostValues', $Sender->EventArguments);
        $EventDate = GetValue('EventDate', $FormPostValues, '');
        // $EventTime = GetValue('EventTime', $FormPostValues, '');
    } // End of DiscussionModel_BeforeSaveDiscussion_Handler
    
    // store new and edited values
    public function DiscussionModel_AfterSaveDiscussion_Handler($Sender) {
        // get what has been entered
        $FormPostValues = GetValue('FormPostValues', $Sender->EventArguments);
        $CategoryID = GetValue('CategoryID', $FormPostValues);

        if ($CategoryID != C('Plugins.EventCalendar.EventCategory')) {
          return;
        }
        
        // marker if this is a new discussion or a correction of an existing one
        $IsNewDiscussion = GetValue('IsNewDiscussion', $FormPostValues);
        $DiscussionID = GetValue('DiscussionID', $FormPostValues);
        $EventDate = GetValue('EventDate', $FormPostValues);
        $EventTime = GetValue('EventTime', $FormPostValues);
        
        if ($IsNewDiscussion) {
            // do an INSERT for a new discussion
            Gdn::SQL()->Insert('EventCalendar_Event', array(
                'EventDate' => Gdn_Format::Text($EventDate),
                'EventTime' => Gdn_Format::Text($EventTime),
                'DiscussionID' => $DiscussionID
            ));
        } else {
            // and an UPDATE for an existing one
            Gdn::SQL()->Update('EventCalendar_Event')
                ->Set('EventDate', Gdn_Format::Text($EventDate))
                ->Set('EventTime', Gdn_Format::Text($EventTime))
                ->Where('DiscussionID', $DiscussionID)
                ->Put();
        }
    } // End of DiscussionModel_AfterSaveDiscussion_Handler


  

} // End of class EventCalendar
