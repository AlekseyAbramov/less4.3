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
        include_once 'connect.php';
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
   
        foreach ($pdo->query($sql) as $row): ?>
            <tr><td><?php echo $row['description'] ?></td>
            <td><?php echo $row['date_added'] ?></td>
            <?php if (!$row['is_done']): ?>
                <td><span style='color: orange;'>В процессе</span></td>
            <?php else: ?>
                <td><span style='color: green;'>Выполнено</span></td>
            <?php endif; ?>
            <td><a href='?id=<?php $row['id'] ?>&action=done'>Выполнить</a>  <a href='?id=<?php $row['id'] ?>&action=delete'>Удалить</a></td>
            <?php if($row['assigned_user_id'] == NULL): ?>
                <td>Вы</td>
            <?php else: ?>
                <td>
                <?php
                $sth =$pdo->prepare("SELECT `login` FROM `user` WHERE id=?");
                $sth->execute(array($row['assigned_user_id']));
                $w = $sth->fetchColumn();
                if($w) {
                    echo $w;
                } ?>
                </td>
            <?php endif; ?>
            <td><?php echo $row['login'] ?></td>
            <td><form method='POST'>  <select name='assigned_user_id'>
            <?php foreach ($pdo->query("SELECT `id`, `login` FROM `user`") as $users): ?>
                <option value='user_<?php echo $users['id'] ?>_task_<?php $row['id'] ?>'><?php echo $users['login'] ?></option> 
            <?php endforeach; ?>
            </select>  <input type='submit' name='assign' value='Переложить ответственность' /></form></td></tr>
        <?php endforeach; ?>
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
            foreach ($pdo->query($sql_select) as $row): ?>
               <tr><td><?php echo $row['description'] ?></td>
               <td><?php echo $row['date_added'] ?></td>
               <?php if (!$row['is_done']): ?>
                    <td><span style='color: orange;'>В процессе</span></td>
               <?php else: ?>
                    <td><span style='color: green;'>Выполнено</span></td>
               <?php endif; ?>
               <td><a href='?id=<?php echo $row['id'] ?>&action=done'>Выполнить</a>  <a href='?id=<?php echo $row['id'] ?>&action=delete'>Удалить</a></td>
               <td>Вы</td>
               <td><?php echo $row['login'] ?></td></tr>
            <?php endforeach; ?>
        </table>
        <p><a href="logout.php">Выйти из системы</a></p>
    </body>
</html>
