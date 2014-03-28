<?php defined('APPLICATION') or exit();?>

<h1><?php echo $this->Data('Title');?></h1>
<div class="Info"><?php echo $this->Data('Info');?></div>
<?php
echo $this->Form->Open();
echo $this->Form->Errors();

echo $this->Form->Label($this->Data('CategoriesLabel'), 'CheckBoxList');
echo $this->Form->CheckBoxList('Plugins.EventCalendar.CategoryIDs', $this->CategoryData, $this->EventCategory, array('ValueField' => 'CategoryID', 'TextField' => 'Name'));

echo $this->Form->Button('Save');
echo $this->Form->Close();

decho($this);