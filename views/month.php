<?php defined('APPLICATION') or die;

$userPhotoFirst = Gdn::config('Vanilla.Comment.UserPhotoFirst', true);

$dayLink = $this->canonicalUrl().'/'.$this->data('Year').'/'.$this->data('Month').'/';
$monthFirst = $this->data('MonthFirst');
$monthLast = $this->data('MonthLast');

?>
<h1 class="CalendarDate">
  <a href="<?= $this->data('CanonicalUrl'), '/', $this->data('PreviousMonth') ?>"><?= t('PreviousMonth', '&laquo') ?></a>
    <?= $this->data('Title') ?>
  <a href="<?= $this->data('CanonicalUrl'), '/', $this->data('NextMonth') ?>"><?= t('NextMonth', '&raquo') ?></a>
</h1>
<ol id="MonthlyCalendar">
<?php
$events = $this->data('Events');
if (count($events) < 1) {
    echo '<p>'.t('No events yet').'</p>';
    return;
}
$event = array_shift($events);
$eventDay = date('j', strtotime($event['EventCalendarDate']));

for ($day = $monthFirst; $day <= $monthLast; $day += 86400) :
    $dayNumber = date('j', $day);
    $weekDay = date('l', $day);
?>
<li class="Day <?= $weekDay ?>">
  <a href="<?= $dayLink, $dayNumber ?>" class="DayLink"><?= $dayNumber ?></a>
<?php if ($dayNumber == $eventDay): ?>
  <dl class="Events">
<?php
while ($dayNumber == $eventDay) :
    $user = Gdn::userModel()->getID($event['UserID']);
?>
    <dt><a class="EventPopup" href="#discussion_<?= $event['DiscussionID'] ?>"><?= $event['Name']?></a></dt>
    <dd>
      <div id="discussion_<?= $event['DiscussionID'] ?>">
        <h2><?= htmlEsc($event['Name']) ?></h2>
        <div class="Item-Header DiscussionHeader">
          <div class="AuthorWrap">
            <span class="Author">
            <?php
              if ($userPhotoFirst) {
                echo userPhoto($user);
                echo t('Organizer: ').userAnchor($user, 'Username');
              } else {
                echo t('Organizer: ').userAnchor($user, 'Username');
                echo userPhoto($user);
              }
            ?>
            </span>
            <span class="AuthorInfo">
            <?php
              echo wrapIf(htmlEsc(val('Title', $user)), 'span', ['class' => 'MItem AuthorTitle']);
              echo wrapIf(htmlEsc(val('Location', $user)), 'span', ['class' => 'MItem AuthorLocation']);
            ?>
            </span>
        </div>
        <div class="Meta DiscussionMeta">
          <span class="MItem DateEvent">
            <?= sprintf(t('Event on %s'), strftime(t('EventCalendar.DateFormat', '%A, %e. %B %Y'), strtotime($event['EventCalendarDate']))) ?>
          </span>
          <span class="MItem DateCreated">
            <?= sprintf(t('Created on %s'), strftime(t('EventCalendar.DateFormat', '%A, %e. %B %Y'), strtotime($event['DateInserted']))) ?>
          </span>
        </div>
      </div>
      <div class="EventBody"><?= Gdn_Format::to($event['Body'], $event['Format']) ?></div>
      <div><?= anchor(t('Go to Discussion'), '/discussion/'.$event['DiscussionID'], 'Button') ?></div>
    </div>
  </dd>
<?php
    $event = array_shift($events);
    $eventDay = date('j', strtotime($event['EventCalendarDate']));
endwhile;
?>
</dl>
<?php endif ?>
</li>
<?php endfor?>
</ol>
