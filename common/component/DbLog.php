<?php
namespace common\component;
use yii\log\DbTarget;

Class DbLog extends DbTarget{
	public function export()
	{
		$tableName = $this->db->quoteTableName($this->logTable);
		$sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]])
		VALUES (:level, :category, :log_time, :prefix, :message)";
		$command = $this->db->createCommand($sql);
		foreach ($this->messages as $message) {
			list($text, $level, $category, $timestamp) = $message;
			if (!is_string($text)) {
				$text = VarDumper::export($text);
			}
			$command->bindValues([
					':level' => $level,
					':category' => $category,
					':log_time' => date("Y-m-d H:i:s",$timestamp),
					':prefix' => $this->getMessagePrefix($message),
					':message' => $text,
			])->execute();
		}
	}
}