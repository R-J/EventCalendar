<?php if (!defined('APPLICATION')) exit();

/**
 * EventCalendar Plugin
 * 
 * @author Robin
 * @license http://opensource.org/licenses/MIT
 */
$PluginInfo['EventCalendar'] = array(
   'Name' => 'Event Calendar',
   'Description' => 'Adds an event date field to new discussions so that they can be treated as events',
   'Version' => '0.2.1',
   'RequiredApplications' => array('Vanilla' => '>=2.0.18'),
   'SettingsUrl' => '/settings/eventcalendar',   
   'RequiredPlugins' => FALSE,
   'RequiredTheme' => FALSE,
   'MobileFriendly' => TRUE,
   'HasLocale' => TRUE,
   'RegisterPermissions' => FALSE,
   'RegisterPermissions' => array('Plugins.EventCalendar.Add',
      'Plugins.EventCalendar.Manage',
      'Plugins.EventCalendar.Notify',
      'Plugins.EventCalendar.View'),
   'Author' => 'Robin'
);

/**
 * Plugin to add date field to New Discussion
 *
 * New discussions could be entered with an "event date".
 * Plugin creates additional views where such discussions are shown on a calendar
 */
class EventCalendarPlugin extends Gdn_Plugin {
   
   /**
    * Make initial changes to Garden
    */   
   public function Setup() {
      $this->Structure();
      // sets route to eventcalendar
      $Router = Gdn::Router();
      $PluginPage = 'vanilla/eventcalendar$1';
      $NewRoute = '^eventcalendar(/.*)?$';
      if(!$Router->MatchRoute($NewRoute)) {
         $Router->SetRoute($NewRoute, $PluginPage, 'Internal');
      }
   } // End of Setup

   /**
    * Adds column for event date to table Discussion
    */   
   public function Structure() {
      $Structure =  Gdn::Structure();
      $Structure->Table('Discussion')
         ->Column('EventCalendarDate', 'date', TRUE)
         ->Set(FALSE, FALSE);
   } // End of Structure

   /**
    * Reset some changes made by the plugin
    */ 
   public function OnDisable() {
      // deletes custom route
      Gdn::Router()-> DeleteRoute('^eventcalendar(/.*)?$');
   }

   /**
    * Allows customization of categories which allow new discussions to be events 
    * Sets config value Plugins.EventCalendar.CategoryIDs
    *
    * @param object $Sender SettingsController
    */   
   public function SettingsController_EventCalendar_Create($Sender) {
      $Sender->Permission('Garden.Settings.Manage');
      $Sender->Title(T('Event Calendar Settings'));
      $Sender->AddSideMenu('settings/EventCalendar');
      $Sender->SetData('Info', T('Event Calendar Info', 'Creation of events can be regulated by category <strong>and</strong> user role. You can set up the categories here, but don\'t forget to assign some permissions in the <a href="/index.php?p=dashboard/role">standard permission section</a> in the dashboard, otherwise you users wouldn\'t be able to use this plugin!'));
      $Sender->SetData('CategoriesLabel', 'Please choose categories in which the creation of events should be allowed');

      $Validation = new Gdn_Validation();
      // $Validation->ApplyRule('Plugins.EventCalendar.CategoryIDs', 'RequiredArray', T('You have to choose at least one category. If you don\'t want to use the plugin any longer, please deactivate it'));
      $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
      $ConfigurationModel->SetField(array('Plugins.EventCalendar.CategoryIDs'));

      $Form = $Sender->Form;
      $Sender->Form->SetModel($ConfigurationModel);

      if ($Sender->Form->AuthenticatedPostBack() != FALSE) {
         if ($Sender->Form->Save() != FALSE) {
            $Sender->StatusMessage = T('Saved');
         }
      } else {
         $Sender->Form->SetData($ConfigurationModel->Data);
      }

      $CategoryModel = new Gdn_Model('Category');
      $Sender->CategoryData = $CategoryModel->GetWhere(array('AllowDiscussions' => 1, 'CategoryID <>' => -1));
      $Sender->EventCategory = C('Plugins.EventCalendar.CategoryIDs');

      $Sender->Render('settings', '', 'plugins/EventCalendar');
   } // End of SettingsController_EventCalendar_Create


   /**
    * Adds menu entry for calendar
    *
    * @param object $Sender Base Controller
    */   
   public function Base_Render_Before($Sender) {
      if(CheckPermission('Plugins.EventCalendar.View') && $Sender->Menu) {
         $Sender->Menu->AddLink(T('Event Calendar'), T('EventCalendar'), 'eventcalendar');
      }
   }
   
   /**
    * Adds input fields to new discussion form
    * Check's for CategoryID, Add and Manage category
    * Allows creation of events in current and next year
    * Datefield is prefilled with current date by eventcalendar.js
    *
    * @param object $Sender PostController
    */ 
   public function PostController_BeforeBodyInput_Handler($Sender) {
      if(!CheckPermission(array('Plugins.EventCalendar.Add', 'Plugins.EventCalendar.Manage'))) {
         return;
      }

      $Sender->AddJsFile('eventcalendar.js', 'plugins/EventCalendar');
      $Sender->AddDefinition('EventCalendarCategoryIDs', json_encode(C('Plugins.EventCalendar.CategoryIDs')));

      // initially don't hide elements in allowed categories
      $CategoryID = $Sender->Discussion->CategoryID;
      if (!in_array($CategoryID, C('Plugins.EventCalendar.CategoryIDs'))) {
         $Hidden = ' Hidden';   
      }

      $Year = date('Y');
      $YearRange = $Year.'-'.($Year + 1);

      $HtmlOut = <<< EOT
<div class="P EventCalendarInput{$Hidden}">
   {$Sender->Form->Label('Event Date', 'EventCalendarDate')}
   {$Sender->Form->Date('EventCalendarDate', array('YearRange' => $YearRange, 'fields' => array('day', 'month', 'year')))}
</div>
EOT;
      echo $HtmlOut;
   } // End of PostController_BeforeBodyInput_Handler

   /**
    *  Add Validation for event date and check permissions
    *
    * @param object $Sender DiscussionModel
    */
   public function DiscussionModel_BeforeSaveDiscussion_Handler($Sender) {
      $Session = Gdn::Session();
      $CategoryID = $Sender->EventArguments['FormPostValues']['CategoryID'];

      // Reset event date and return if wrong category or no right to add event
      if (!in_array($CategoryID, C('Plugins.EventCalendar.CategoryIDs')) || !$Session->CheckPermission(array('Plugins.EventCalendar.Add', 'Plugins.EventCalendar.Manage'))) {
         $Sender->EventArguments['FormPostValues']['EventCalendarDate'] = '';
         return;
      }

      // Add custom validation text
      $Sender->Validation->ApplyRule('EventCalendarDate', 'Required', T('Please enter an event date'));
      $Sender->Validation->ApplyRule('EventCalendarDate', 'Date', T('The event date you\'ve entered is invalid'));
   } // End of DiscussionModel_BeforeSaveDiscussion_Handler

   /**
    * Returns nicely formatted html for an event date 
    *
    * @param date $EventDate Date of the event
    * @return string html string showing the event date (translatable)
    */ 
   private function FormatEventCalendarDate($EventDate, $IncludeIcon = FALSE) {
      if(!CheckPermission(array('Plugins.EventCalendar.View'))) {
         return;
      }
      if ($EventDate != '0000-00-00') {
         if ($IncludeIcon) {
            $Icon = '<img src="'.SmartAsset('/plugins/EventCalendar/design/images', TRUE).'/eventcalendar.png" />';
         } else {
            $Icon = '';
         }
         return Gdn_Format::Date($EventDate, T('EventCalendarDateFormat', "<div id=\"EventCalendarDate\">{$Icon}On %A, %e. %B %Y</div>"));
      }
   } // End of FormatEventCalendarDate

   /**
    * Add event date to discussion title in discussion
    *
    * @param object $Sender DiscussionController
    */    
   public function DiscussionController_AfterDiscussionTitle_Handler($Sender, $Args) {
      echo $this->FormatEventCalendarDate($Sender->EventArguments['Discussion']->EventCalendarDate, TRUE);
   }

   /**
    * Add event date to discussion title in discussions overview
    *
    * @param object $Sender DiscussionsController
    */    
   public function DiscussionsController_AfterDiscussionTitle_Handler($Sender){
      echo $this->FormatEventCalendarDate($Sender->EventArguments['Discussion']->EventCalendarDate, TRUE);
   }

   /**
    * Add event date to discussion title in categories overview
    *
    * @param object $Sender CategoriesController
    */    
   public function CategoriesController_AfterDiscussionTitle_Handler($Sender){
      echo $this->FormatEventCalendarDate($Sender->EventArguments['Discussion']->EventCalendarDate);
   }

   /**
    * Handles different views (only monthly overview by now)
    *
    * @param object $Sender VanillaController
    * @param array $Args /Year/Month to show
    */    
   public function VanillaController_EventCalendar_Create($Sender, $Args = array()) {
      $Sender->Permission('Plugins.EventCalendar.View');
      
      $Sender->ClearCssFiles();
      $Sender->AddCssFile('style.css');
      $Sender->AddCssFile('eventcalendar.css', 'plugins/EventCalendar');
      $Sender->AddJsFile('eventcalendar.js', 'plugins/EventCalendar');
      $Sender->MasterView = 'default';
      $Sender->AddModule('NewDiscussionModule');
      $Sender->AddModule('CategoriesModule');
      $Sender->AddModule('BookmarkedModule');

      // only show current year +/- 1
      $Year = $Args[0];
      $CurrentYear = date('Y');
      if ($Year < $CurrentYear -1 || $Year > $CurrentYear + 1) {
         $Year = $CurrentYear;
      }
      // sanitize month
      $Month = sprintf("%02s", $Args[1]);
      if ($Month < 1 || $Month > 12) {
         $Month = date('m');
      }

      $MonthFirst = mktime(0, 0, 0, $Month, 1, $Year);
      $DaysInMonth = date('t', $MonthFirst);
      $MonthLast = mktime(0, 0, 0, $Month, $DaysInMonth, $Year);
      $Sender->CanonicalUrl(Url('eventcalendar', TRUE));
      $Sender->SetData('Title', T('Event Calendar'));
      $Sender->SetData('Breadcrumbs', array(array('Name' => T('Event Calendar'), 'Url' => '/eventcalendar')));
      $Sender->SetData('Month', $Month);
      $Sender->SetData('Year', $Year);
      $Sender->SetData('MonthFirst', $MonthFirst);
      $Sender->SetData('MonthLast', $MonthLast);
      $Sender->SetData('PreviousMonth', date('Y', $MonthFirst - 1).'/'.date('m', $MonthFirst - 1));
      $Sender->SetData('NextMonth', date('Y', $MonthLast + 86400).'/'.date('m', $MonthLast + 86400));
      $Sender->SetData('DaysInMonth', $DaysInMonth);
      $Sender->SetData('Events', EventCalendarModel::Get("{$Year}-{$Month}-01", "{$Year}-{$Month}-{$DaysInMonth}"));

      $ViewName = 'month';
      $Sender->Render($ViewName, '', 'plugins/EventCalendar');
   }
}

