<?php

/**
* Класс для работы по заданному ID с полями `name` и `status` таблицы `objects` в режиме чтение/запись
*
* @property-read int $id
* @property-read string $name
* @property-read int $status
* @property-read bool $changed
* @property-write string $name
* @property-write string $name
* @version 1.0
* @author Yury Litvinenko <yury.litwinenko@mail.ru>
*/
final class Item {
	private int $id;
	private string $name;
	private int $status;
	private bool $changed = false;


	public function __construct(int $id) {
		$this->id = $id;
		$this->init();
	}

	/**
	* Извлекает из таблицы `objects` значений полей `name`,`status` и записывает их в соответствующие свойства
	*
	* Предусмотрен одноразовый вызов метода в целях экономии ресурсов
	* @return true|false true - при первом запуска, false - при последующих
	*/
	private function init() {
		// Обеспечивает одноразовый вызов метода
		if (isset($this->name) && isset($this->status))
			return false;

		// $mysqli =  ...
		$result = $mysqli->query('SELECT `name`, `status` FROM `objects` WHERE `id`=' . $this->id);
		$row = $result->fetch_array();
		$this->name = $row['name'];
		$this->status = $row['status'];

		return true;
	}

	/**
	* Магически возвращает значения свойств
	*/
	public function __get($property) {
		if (property_exists($this, $property))
			return $this->$property
	}

	/**
	* Устанавливает значения для доступных свойств с проверкой переданных данных
	*/
	public function __set($property, $value) {
		$available = ['name', 'status'];

		// Условные фильтры для записи значений
		if ( !property_exists($this, $property) || in_array($property, $available) )
			return NULL;
		if (empty(trim($value)) || gettype($property) != gettype($this->$property))
			return NULL;

		$this->$property = $value;
		$this->changed = true;
	}

	/**
	* Записывает в таблицу значения свойств $name и $status, если они были изменены ($changed)
	* @return null|false|true = ничего сохранено не было | ошибка при сохранение | сохранено успешно
	*/
	public function save() {
		if (!$this->changed)
			return NULL;

		$name = $mysqli->real_escape_string($this->name);
		$status = $mysqli->real_escape_string($this->status);
		return $mysqli->query("UPDATE `objects` SET `name`='{$name}', `status`='{$status}' WHERE `id`=". $this->id);
	}

}
