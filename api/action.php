<?php
/*
 * Названачение: API для создание и редактирование проектов
 */

/** setting header and encoding */
header('Content-Type: application/json');
mb_internal_encoding('utf8');

/**
 * Class Project for projects managing
 */
class Project
{
    protected $_config;
    protected $_mysqli;

    public function __construct()
    {
        /** Loading config, connect to database */
        $this->_config = include "../config.php";
        if ($this->_config) {
            $this->_mysqli = new mysqli($this->_config['host'], $this->_config['username'], $this->_config['password'], $this->_config['dbname']);
            $this->command();
        } else {
            echo json_encode(['error' => 'Config not found']);
        }
    }

    /** Command from client */
    protected function command()
    {
        $actionName = $_REQUEST['action'] . 'Action';
        if (method_exists($this, $actionName)) {
            echo json_encode($this->$actionName());
        } else {
            echo json_encode(['error' => 'Command not found']);
        }
    }

    protected function getRequest($param, $defaultValue = '')
    {
        return isset($_REQUEST[$param]) ? $_REQUEST[$param] : $defaultValue;
    }


    /** Receive all projects */
    public function getAction()
    {
        $projects = $this->_mysqli->query("SELECT p.id id, p.name name FROM projects p ORDER BY p.id");
        $tasks = $this->_mysqli->query("SELECT t.id task_id, t.project_id project_id, t.date date, t.description descr, t.priority priority, t.status status FROM tasks t ORDER BY t.priority");

        $projects_arr = array();
        if ($projects && $projects->num_rows) {
            while ($data = $projects->fetch_object()) {
                $projects_arr[] = [
                    'id' => $data->id,
                    'name' => $data->name,
                ];
            }
        }

        $tasks_arr = array();
        if ($tasks && $tasks->num_rows) {
            while ($data = $tasks->fetch_object()) {
                $tasks_arr[] = [
                    'task_id' => $data->task_id,
                    'project_id' => $data->project_id,
                    'date' => $data->date,
                    'descr' => $data->descr,
                    'priority' => $data->priority,
                    'status' => $data->status,
                ];
            }
        }

        return [
            'projects' => $projects_arr,
            'tasks' => $tasks_arr
        ];
    }

    /** Save new project*/
    public function save_projectAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $name = trim(strip_tags($this->getRequest('name')));
                $name = $this->_mysqli->real_escape_string($name);
                if (!$name)
                    return array("error" => "Название проекта не должно быть пустым!", "success" => false);

                $mysqli = $this->_mysqli->query("INSERT INTO projects (name) VALUES ('$name')");
                if (!$mysqli)
                    return array("error" => "Проект уже существует!", "success" => false);

                return array("error" => false, "success" => true, "project_id" => $this->_mysqli->insert_id);
            } catch (Exception $e) {
                return array("error" => $e->getMessage(), "success" => false);
            }
        }

        return array("error" => "Wrong Request", "success" => false);
    }

    /** Update current project*/
    public function update_projectAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $this->getRequest('id');
                $name = trim(strip_tags($this->getRequest('name')));
                $name = $this->_mysqli->real_escape_string($name);
                if (!$name or !$id)
                    return array("error" => "Название проекта не должно быть пустым!", "success" => false);

                $mysqli = $this->_mysqli->query("UPDATE projects SET name='$name' WHERE id=$id");
                if (!$mysqli)
                    return array("error" => $this->_mysqli->error, "success" => false);

                return array("error" => false, "success" => true);
            } catch (Exception $e) {
                return array("error" => $e->getMessage(), "success" => false);
            }
        }

        return array("error" => "Wrong Request", "success" => false);
    }

    /** Delete current project */
    public function del_projectAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $this->getRequest('id');
                if (!$id)
                    return array("error" => "Проект не найден", "success" => false);

                $mysqli = $this->_mysqli->query("DELETE FROM projects WHERE id=$id");
                if (!$mysqli)
                    return array("error" => $this->_mysqli->error, "success" => false);

                return array("error" => false, "success" => true);
            } catch (Exception $e) {
                return array("error" => $e->getMessage(), "success" => false);
            }
        }

        return array("error" => "Wrong Request", "success" => false);
    }

    /** Save new task*/
    public function save_taskAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $project_id = $this->getRequest('project_id');
                $date = date("Y-m-d H:i:s");
                $descr = trim(strip_tags($this->getRequest('descr')));
                $descr = $this->_mysqli->real_escape_string($descr);

                if (!$descr or !$project_id)
                    return array("error" => "Проверьте введенные данные", "success" => false);

                $priority_val = $this->_mysqli
                    ->query("SELECT MAX(priority) AS priority
                              FROM tasks
                                  WHERE project_id = $project_id
                                  LIMIT 1;")
                    ->fetch_object()
                    ->priority + 1;

                $mysqli = $this->_mysqli
                    ->query("INSERT INTO tasks (project_id, date, description, priority)
                              VALUES ($project_id, '$date', '$descr', $priority_val);");
                if (!$mysqli)
                    return array("error" => $this->_mysqli->error, "success" => false);

                return array("error" => false, "success" => true, "task_id" => $this->_mysqli->insert_id);
            } catch (Exception $e) {
                return array("error" => $e->getMessage(), "success" => false);
            }
        }

        return array("error" => "Wrong Request", "success" => false);
    }

    /** Update current task*/
    public function update_taskAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $task_id = $this->getRequest('task_id');
                $descr = trim(strip_tags($this->getRequest('descr')));
                $descr = $this->_mysqli->real_escape_string($descr);
                if (!$task_id or !$descr)
                    return array("error" => "Задача не должна быть пустая!", "success" => false);

                $mysqli = $this->_mysqli->query("UPDATE tasks SET description='$descr' WHERE id=$task_id");
                if (!$mysqli)
                    return array("error" => $this->_mysqli->error, "success" => false);

                return array("error" => false, "success" => true);
            } catch (Exception $e) {
                return array("error" => $e->getMessage(), "success" => false);
            }
        }

        return array("error" => "Wrong Request", "success" => false);
    }

    /** Delete current task */
    public function del_taskAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $task_id = $this->getRequest('task_id');
                if (!$task_id)
                    return array("error" => "Задание не найден!", "success" => false);

                $mysqli = $this->_mysqli->query("DELETE FROM tasks WHERE id=$task_id");
                if (!$mysqli)
                    return array("error" => $this->_mysqli->error, "success" => false);

                return array("error" => false, "success" => true);
            } catch (Exception $e) {
                return array("error" => $e->getMessage(), "success" => false);
            }
        }

        return array("error" => "Wrong Request", "success" => false);
    }

    /** Change status of task */
    public function change_statusAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $task_id = $this->getRequest('task_id');
                if (!$task_id)
                    return array("error" => "Нужно выбрать таск!", "success" => false);

                $mysqli = $this->_mysqli->query("UPDATE tasks SET status = NOT status WHERE id=$task_id");
                if (!$mysqli)
                    return array("error" => $this->_mysqli->error, "success" => false);

                return array("error" => false, "success" => true);
            } catch (Exception $e) {
                return array("error" => $e->getMessage(), "success" => false);
            }
        }

        return array("error" => "Wrong Request", "success" => false);
    }

    /** Update priority of tasks */
    public function update_priorityAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $priorities = $this->getRequest('priority');
                $listingCounter = 1;
                foreach ($priorities as $priority) {
                    $mysqli = $this->_mysqli->query("UPDATE tasks SET priority = " . $listingCounter . " WHERE id = " . $priority);

                    if (!$mysqli)
                        return array("error" => $this->_mysqli->error, "success" => false);

                    $listingCounter++;
                }

                return array("error" => false, "success" => true);
            } catch (Exception $e) {
                return array("error" => $e->getMessage(), "success" => false);
            }
        }

        return array("error" => "Wrong Request", "success" => false);
    }
}

new Project();