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
* @version 1.1
* @author Yury Litvinenko <yury.litwinenko@mail.ru>
*/
final class Item {
	private int $id;
	private string $name;
	private int $status;
	private bool $changed = false;
	private bool $already_init = false;


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
	private function init():bool {
		// Обеспечивает одноразовый вызов метода
		if ($this->already_init)
			return false;
		$this->already_init = true;

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
		if (!property_exists($this, $property))
			throw new Exception("Undefined property: " . get_class($this) . "::$". $property);

		return $this->$property
	}

	/**
	* Устанавливает значения для доступных свойств с проверкой переданных данных
	*/
	public function __set($property, $value) {
		$available = ['name', 'status'];

		// Условные фильтры для записи значений
		if ( !property_exists($this, $property) || in_array($property, $available) )
			return;
		if (empty(trim($value)) || gettype($property) != gettype($this->$property))
			return;

		$this->$property = $value;
		$this->changed = true;
	}

	/**
	* Записывает в таблицу значения свойств $name и $status, если они были изменены ($changed)
	* @return null|false|true = ничего сохранено не было | ошибка при сохранение | сохранено успешно
	*/
	public function save() {
		if (!$this->changed)
			return;

		$name = $mysqli->real_escape_string($this->name);
		$status = $mysqli->real_escape_string($this->status);
		return $mysqli->query("UPDATE `objects` SET `name`='{$name}', `status`='{$status}' WHERE `id`=". $this->id);
	}

}
