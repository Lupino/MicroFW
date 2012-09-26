<?
/* Returns true if valid email */
function preg_email($email = ""){
	if (preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",$email)){
		return true;
	}
	return false;
}
//---------------
//require_once('Query.php');
//----------------------------------------
class Model extends Context{
	protected $deny;
	protected $error=array();
	public function errorInfo(){
		return new Context($this->error);
	}
	protected function e($label,$info){
		$this->error[$label]=empty($this->error[$label])?$info:$this->error[$label]."<br/>".$info;
	}
	protected function query($parameter, $default = null){
		if(!empty($this->_context[$parameter]))return $this->_context[$parameter];
		foreach($this->props as $key=>$val){
			if($parameter==$key){
				$props=array('HAS_MANY','HAS_ONE');
				$prop='';
				foreach($props as $p){
					if(!empty($val[$p])){
						$prop=$p;
						$db=$val[$p];
						$query=$db::find("{$val['target_key']}=?");
						switch($prop){
							case 'HAS_MANY':
								return $query->getAll(array($this->_context[$val['source_key']]));
							break;
							case 'HAS_ONE':
								return $query->getOne(array($this->_context[$val['source_key']]));
							break;
							default:
							break;
						}
					break;
					}
				}
			}
		}
		return $default;
	}
	//----------------------------
	public function cleaner($value,$conditions){
		$conditions = explode(" ",$conditions);
		foreach ($conditions as $key => $condition){
			if(strstr($condition,"Default")){
				$value=substr($condition,7,strlen($condition));
				continue;
			}
			switch ($condition){
				case 'int':
					return preg_replace('/[^0-9]/','',$value);
				break;
				case 'alpha_numeric':
					return preg_replace('/[^0-9a-z]/i','',$value);
				break;
				case 'alpha_plus':
					return preg_replace('/[^0-9 \.a-z]/i','',$value);
				break;
				default:
				break;
			}
		}

		return $value;
	}

	public function clean(){
		// clean inputs
		foreach ($this->columns as $column_name => $rules){
			$this->_context[$column_name] = $this->cleaner(!empty($this->_context[$column_name])?$this->_context[$column_name]:'', $rules);
		}
	}

	public function validater($value,$conditions,$column_name){
		$this->clean();
		$label = $column_name;
		$error_count = 0;
		if (!strstr($conditions, 'required')){
			// if required, fail, otherwise, just return cleaned
			return true;
		}
		$conditions = explode(" ",$conditions);
		foreach ($conditions as $key => $condition){
			switch ($condition){
				case 'email':
					if (!preg_email($value))	{
						$error_count++;
						$this->e($label,'Invalid email address.');
					}
				break;
				case 'unique':
					$sql="select * from {$this->table} where $column_name = '".$value."'";
					/*if (!empty($this->_context['id']))
					{
						$sql.=" and id != '".$this->_context['id']."'";
					}*/
					$st=Query::getDBinit()->prepare($sql);
					$rs=$st->getOne();
					if (!empty($rs[$column_name])){
						$error_count++;
						$this->e($label,$value . ' already in use');
					}
				break;
				case 'int':
					if (preg_match('/[^0-9]/',$value)){
						$error_count++;
						$this->e($label, ' must be a whole number.');
					}
				break;
				case 'positive':
					if (preg_match('/[^0-9]/',$value) || $value <= 0){
						$error_count++;
						$this->e($label, ' must be greater than 0.');
					}
				break;
				case 'required':
					if ($value === false || empty($value)){
						$error_count++;
						$this->e($label, ' required');
					}
				break;
				case 'my':
				global $is_admin;
					if ($value != $_SESSION['user_id'] && !$is_admin){//&& $_GET['o'] != 1 for migration
						$error_count++;
						$this->e($label,'You dont have permission to do that.');
					}
				break;
				default:
				break;
			}
		}
		if ($error_count){
			return false;
		}
		return true;
	}

	public function validate(){
		// do validation
		$validation_errors = 0;
		$valid_array = array();
		foreach ($this->columns as $column_name => $validation_rules){
			if(strstr($validation_rules,'auto_increment'))continue;
			if(strstr($validation_rules,$this->deny))continue;
			if ($this->validater(!empty($this->_context[$column_name])?$val=$this->_context[$column_name]:'', $validation_rules, $column_name)){
				$valid_array[$column_name] = !empty($this->_context[$column_name])?$this->_context[$column_name]:'';
			}else{
				$validation_errors++;
			}

		}
		if ($validation_errors)return false;
		$this->validated = $valid_array;
		return true;
	}

	public function create(){
		if (isset($this->columns['timestamp'])){
			$this->timestamp = time();
		}
		if (isset($this->columns['ip'])){
			$this->_context['ip'] = $_SERVER['REMOTE_ADDR'];
		}
		if (isset($this->columns['user_agent'])){
			$this->_context['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		}
		if (isset($this->columns['user_id']) && isset($_SESSION['user_id']) && !isset($this->_context['user_id'])){
			$this->_context['user_id'] = $_SESSION['user_id'];
		}
		$this->deny='deny insert';
		if ($this->validate()) {
			if ($this->_context['id'] = Query::insert_array($this->validated, $this->table)){
				return true;
			}

		}
		return false;
	}

	public function update()	{
		$this->deny='deny update';
		if ($this->validate()) {
			if (Query::update_array($this->validated, $this->_context['id'], $this->table)){
				return true;
			}
			return true;
		}
		return false;
    }
    public function save(){
        if(!$this->id||$this->id==0){
            return $this->create();
        }
        return $this->update();
    }
}

?>
