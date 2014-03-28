<?php if (!defined('APPLICATION')) exit(); 

$Year = $this->Data('Year');
$Month = $this->Data('Month');
$MonthFirst = $this->Data('MonthFirst');
$MonthLast = $this->Data('MonthLast');
$Domain = Gdn_Url::WebRoot(TRUE);

$Events = $this->Data('Events');
$Event = array_shift($Events);
$EventDay = $Event['EventCalendarDay'];

/*
<div class="Tabs HeadingTabs CalendarTabs">
   <div class="SubTab"><?php echo date(T('F Y'), $this->Data('MonthFirst'));?></div>
</div>
*/
?>

<h1 class="CalendarDate">
<a href="<?php echo "{$Domain}eventcalendar/{$this->Data('PreviousMonth')}"; ?>">«</a>
<?php echo date(T('F Y'), $this->Data('MonthFirst'));?>
<a href="<?php echo "{$Domain}eventcalendar/{$this->Data('NextMonth')}"; ?>">»</a>
</h1>
<ol id="MonthlyCalendar">

<?php
for ($Day = $MonthFirst; $Day <= $MonthLast; $Day += 86400) {
   $DayNumber = date('j', $Day);
   $WeekDay = date('l', $Day);
?>
   <li class="Day <?php echo $WeekDay;?>">
      <a href="<?php echo "{$Domain}eventcalendar/$Year/$Month/$DayNumber";?>" class="DayLink"><?php echo $DayNumber;?></a>
<?php   
   if ($DayNumber == $EventDay) {
?>
      <dl class="Events">
<?php
      while ($DayNumber == $EventDay) {
?>         
         <dt><?php echo $Event['Name'];?></dt>
         <dd>
            <div class="EventOrganizer"><?php echo $Event['Organizer'];?></div>
            <div class="EventBody"><?php echo $Event['Body'];?></div>
         </dd>
<?php
         $Event = array_shift($Events);
         $EventDay = $Event['EventCalendarDay'];
      }
?>
      </dl>
<?php   
   }
?>
   </li>
<?php
}
?>    
</ol>  
