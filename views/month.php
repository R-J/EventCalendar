<?php if (!defined('APPLICATION')) exit(); 

$UserPhotoFirst = C('Vanilla.Comment.UserPhotoFirst', TRUE);

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
         $User = Gdn::UserModel()->GetID($Event['UserID']);
?>         
         <dt><?php echo $Event['Name'];?></dt>
         <dd>

<div class="Popup EventPopup">
   <div class="Border">
      <div class="Body">
         <div class="Content">
            <div class="EventBody">
               <h1><?php echo $Event['Name'];?></h1>

               <div class="Item-Header DiscussionHeader">
                  <div class="AuthorWrap">
                     <span class="Author">
                        <?php
                        if ($UserPhotoFirst) {
                           echo UserPhoto($User);
                           echo T('Organizer: ').UserAnchor($User, 'Username');
                        } else {
                           echo T('Organizer: ').UserAnchor($User, 'Username');
                           echo UserPhoto($User);
                        }
                        ?>
                     </span>
                     <span class="AuthorInfo">
                        <?php
                        echo WrapIf(htmlspecialchars(GetValue('Title', $User)), 'span', array('class' => 'MItem AuthorTitle'));
                        echo WrapIf(htmlspecialchars(GetValue('Location', $User)), 'span', array('class' => 'MItem AuthorLocation'));
                        ?>
                     </span>
                  </div>
                  <div class="Meta DiscussionMeta">
                     <span class="MItem DateEvent">
                        <?php
                        echo T('Event on ').Anchor(Gdn_Format::Date($Event['EventCalendarDate'], 'html'), $Event['Url'], 'Permalink', array('rel' => 'nofollow'));
                        ?>
                     </span>
                     <span class="MItem DateCreated">
                        <?php
                        echo T('(Created on ').Anchor(Gdn_Format::Date($Event['DateInserted'], 'html'), $Event['Url'], 'Permalink', array('rel' => 'nofollow')).')';
                        ?>
                     </span>
                     <?php
         //               echo DateUpdated($Discussion, array('<span class="MItem">', '</span>'));
                     ?>
                  </div>
               </div>

               <div><?php echo $Event['Body'];?></div>
            </div>
         </div>
      </div>
   </div>
</div>
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
