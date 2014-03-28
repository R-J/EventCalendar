<?php if (!defined('APPLICATION')) exit();

class EventCalendarModel extends VanillaModel {
   public function Get($Begin, $End, $Offset = '0', $Limit = '') {
      // Validate parameters, set today as default
      $BeginDate = strtotime($Begin);
      if ($BeginDate <= 0) {
         $BeginDate = date('Y-m-d');
      } else {
         $BeginDate = date('Y-m-d', $BeginDate);
      }
      $EndDate = strtotime($End);
      if ($EndDate <= 0) {
         $EndDate = date('Y-m-d');
      } else {
         $EndDate = date('Y-m-d', $EndDate);
      }
      if (!is_numeric($Offset)) {
         $Offset = 0;
      }
      if (!is_numeric($Limit)) {
         $Limit = '';
      }
      
      $Sql = GDN::SQL();
      $Sql->Select('d.Name, d.Body, d.Format')
         ->Select('d.InsertUserID', '', 'UserID')
         ->Select('DAY FROM d.EventCalendarDate', 'EXTRACT', 'EventCalendarDay')
         ->From('Discussion d')
         ->Where('d.EventCalendarDate >=', $BeginDate)
         ->Where('d.EventCalendarDate <=', $EndDate)
         ->OrderBy('d.EventCalendarDate')
         ->Limit($Limit, $Offset);

      // add permission restrictions if necessary 
      $Perms = DiscussionModel::CategoryPermissions();
      if ($Perms !== TRUE) {
         $Sql->WhereIn('d.CategoryID', $Perms);
      }
      // return $Sql->GetSelect();
      return $Sql->Get()->ResultArray();
   }
}
