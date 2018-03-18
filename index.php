<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <style>
            table { 
                border-spacing: 0;
                border-collapse: collapse;
            }

            table td, table th {
                border: 1px solid #ccc;
                padding: 5px;
            }

            table th {
                background: #eee;
            }
            
            form {
                margin-bottom: 10px;
            }
        </style>
        
        <?php
        session_start();
        if(empty($_SESSION['user'])) {
        ?>
        <a href="login.php">Войдите на сайт</a>
        <?php
         die;
        }    
        ?>

        <h1>Список дел пользователя <?php echo $_SESSION['user']; ?></h1>
        <div style="float: left">
            <form method="POST">
                <input type="text" name="description" placeholder="Описание задачи" value="" />
                <input type="submit" name="save" value="Добавить" />
            </form>
        </div>
        <div style="float: left; margin-left: 20px;">
            <form method="POST">
                <label for="sort">Сортировать по:</label>
                <select name="sort_by">
                    <option value="date_added">Дате добавления</option>
                    <option value="is_done">Статусу</option>
                    <option value="description">Описанию</option>
                </select>
                <input type="submit" name="sort" value="Отсортировать" />
            </form>
        </div>
        <div style="clear: both"></div>

        <table>
            <tr>
                <th>Описание задачи</th>
                <th>Дата добавления</th>
                <th>Статус</th>
                <th></th>
                <th>Ответственный</th>
                <th>Автор</th>
                <th>Закрепить задачу за пользователем</th>
            </tr>
        <?php
        
        $pdo = new PDO("mysql:host=localhost;dbname=aabramov;charset=utf8", "aabramov", "neto1499");
        $sql_sort = "";
        $user_id = $_SESSION['id'];
        if (!empty($_POST)){
            if (!empty($_POST["description"])) {
                $descritp = strip_tags($_POST["description"]);
                $sql = "INSERT INTO `task`(`user_id`, `description`, `date_added`) VALUES ('$user_id','$descritp',NOW())";
                $pdo->query($sql);
                header("Location: ".$_SERVER['REQUEST_URI']);
            }
            if (!empty($_POST["sort"])){
                $sort = $_POST["sort_by"];
                $sql_sort = "SELECT task.id, task.user_id, task.assigned_user_id, task.description, task.is_done, task.date_added, user.login FROM `task` LEFT JOIN user ON task.user_id=user.id WHERE user_id='$user_id' ORDER BY ". $sort;
                $pdo->query($sql_sort);
            }
            if(!empty($_POST["assign"])) {
                $assign = explode("_", $_POST["assigned_user_id"]);
                var_dump($assign);
                $sql_update = "UPDATE `task` SET assigned_user_id='$assign[1]' WHERE id='$assign[3]'";
                $pdo->query($sql_update);
                header("Location: /less4.3/index.php");
            }
        }
        if (!empty($_GET)){
            if ($_GET["action"] == "done"){
                $id = $_GET["id"];
                $sql = "UPDATE `task` SET is_done=100 WHERE id='$id'";
                $pdo->query($sql);
                header("Location: /less4.3/index.php");
            }
            if ($_GET["action"] == "delete"){
                $id = $_GET["id"];
                $sql = "DELETE FROM `task` WHERE `task`.`id` = '$id'";
                $pdo->query($sql);
                header("Location: /less4.3/index.php");
            }
        }
        if (strlen($sql_sort)){
            $sql = $sql_sort;
        } else {
            $sql = "SELECT task.id, task.user_id, task.assigned_user_id, task.description, task.is_done, task.date_added, user.login FROM `task` LEFT JOIN user ON task.user_id=user.id WHERE user_id='$user_id'";
        }
   
        foreach ($pdo->query($sql) as $row) {
            echo "<tr><td>". $row['description'] . "</td>";
            echo "<td>". $row['date_added'] . "</td>";
            if (!$row['is_done']){
                echo "<td><span style='color: orange;'>В процессе</span></td>";
            } else {
                echo "<td><span style='color: green;'>Выполнено</span></td>";
            }
            echo "<td><a href='?id=". $row['id']. "&action=done'>Выполнить</a>". "  ". "<a href='?id=". $row['id']. "&action=delete'>Удалить</a></td>";
            if($row['assigned_user_id'] == NULL) {
             echo "<td>Вы</td>";   
            } else {
                echo "<td>"; 
                $sth =$pdo->prepare("SELECT `login` FROM `user` WHERE id=?");
                $sth->execute(array($row['assigned_user_id']));
                $w = $sth->fetchColumn();
                if($w) {
                    echo $w;
                }
                echo "</td>";
            }
            echo "<td>". $row['login']. "</td>";
            echo "<td><form method='POST'>  <select name='assigned_user_id'> "; 
            foreach ($pdo->query("SELECT `id`, `login` FROM `user`") as $users) {
                echo "<option value='user_". $users['id']."_task_". $row['id']. "'>". $users['login']. "</option> "; 
            }
            echo "  </select>  <input type='submit' name='assign' value='Переложить ответственность' /></form></td></tr>";
        }
        ?>
        </table>    
        <p><strong>Также, посмотрите, что от Вас требуют другие люди:</strong></p>
        <table>
            <tr>
                <th>Описание задачи</th>
                <th>Дата добавления</th>
                <th>Статус</th>
                <th></th>
                <th>Ответственный</th>
                <th>Автор</th>
            </tr>
            <?php
            $sql_select = "SELECT task.id, task.user_id, task.assigned_user_id, task.description, task.is_done, task.date_added, user.login FROM `task` LEFT JOIN user ON task.user_id=user.id WHERE assigned_user_id='$user_id'";
            foreach ($pdo->query($sql_select) as $row) {
               echo "<tr><td>". $row['description'] . "</td>";
               echo "<td>". $row['date_added'] . "</td>";
               if (!$row['is_done']){
                    echo "<td><span style='color: orange;'>В процессе</span></td>";
               } else {
                    echo "<td><span style='color: green;'>Выполнено</span></td>";
                 }
               echo "<td><a href='?id=". $row['id']. "&action=done'>Выполнить</a>". "  ". "<a href='?id=". $row['id']. "&action=delete'>Удалить</a></td>";
               echo "<td>Вы</td>";
               echo "<td>". $row['login']. "</td>";
            }
            var_dump($user_id);
            ?>
        </table>
        <p><a href="logout.php">Выйти из системы</a></p>
    </body>
</html>
