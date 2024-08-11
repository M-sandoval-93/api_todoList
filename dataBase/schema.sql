-- CREATE SCHEMA `todoList` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;

-- create table privilege (
-- 	id_privilege int auto_increment primary key,
--     privilege varchar(120) not null
-- );

-- create table user (
-- 	id_user int auto_increment primary key,
--     name_user varchar(80) not null,
--     email_user varchar(200) unique not null,
--     password_user varchar(120) not null,
--     id_privilege int not null,
--     state_user boolean default true,
--     first_login timestamp, 
--     foreign key (id_privilege) references privilege(id_privilege)
-- );

-- create table todo (
-- 	id_todo int auto_increment primary key,
--     title_todo varchar(255) not null,
--     state_todo boolean default false,
--     date_create_todo timestamp default now(),
--     date_complete_todo timestamp,
--     id_user int not null,
--     foreign key (id_user) references user(id_user) on delete cascade
-- );

-- create table detail_todo (
-- 	id_detail_todo int auto_increment primary key,
--     description_todo varchar(255) not null,
--     id_todo int not null,
--     foreign key (id_todo) references todo(id_todo) on delete cascade
-- );

-- create table share_todo (
-- 	id_share_todo int auto_increment primary key,
--     id_todo int not null,
--     id_user int not null,
--     shared_with_id_user int not null,
--     permission_to_edit boolean not null,
--     foreign key (id_todo) references todo(id_todo) on delete cascade,
--     foreign key (id_user) references user(id_user) on delete cascade,
--     foreign key (shared_with_id_user) references user(id_user) on delete cascade
-- );

-- -- select normales sobre las tablas
-- select * from user
-- select * from privilege
-- select * from todo
-- select * from share_todo

-- -- select del share todo para obtener todos los todo normales y compartidos
-- select todo.*, share_todo.shared_with_id_user
-- from todo
-- left join share_todo on todo.id_todo = share_todo.id_share_todo
-- where todo.id_user = 2 or share_todo.shared_with_id_user = 2

-- -- insert de datos
-- -- ingreso de privilegios 
-- insert into privilege (privilege) values ('admin');

-- -- ingreso de usuarios
-- insert into user (name_user, email_user, password_user, id_privilege) values ('sandoval', 'sandoval@gmail.com', 'sandoval', 1);
-- insert into user (name_user, email_user, password_user, id_privilege) values ('vasquez', 'vasquez@gmail.com', 'vasquez', 1);

-- -- ingreso de algunos todo
-- insert into todo (title_todo, id_user) values ('first todo', 1); 
-- insert into todo (title_todo, id_user) values ('second todo', 1);
-- insert into todo (title_todo, id_user) values ('last', 1);

-- -- compartir algun todo
-- insert into share_todo (id_todo, id_user, shared_with_id_user, permission_to_edit) values (1, 1, 2, true)

-- NEW SCHEMA SQL FOR TASK LIST
CREATE SCHEMA `db_task_list` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci ;

-- tabla para registrar los privilegios que tendrá un usuario
-- admin, standar, visualizador, editor
create table if not exists privilege (
	id_privilege int auto_increment primary key,
    privilege varchar(80) not null unique,
    description_privilege varchar(255) not null,
    record_creation_date timestamp default current_timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- tabla para el registro de cuentas de usuario
create table if not exists user_account (
	id_user_account int auto_increment primary key,
	user_name varchar(80) not null,
    user_password varchar(120) not null,
    user_email varchar(120) not null unique,
    id_privilege int not null default 2,
    state_account boolean not null default false,
    first_access timestamp,
    last_access timestamp,
    record_creation_date timestamp default current_timestamp,
    foreign key (id_privilege) references privilege(id_privilege) on update cascade on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- tabla para verificar cuenta de usuario creada
create table if not exists email_verification (
	id_email_verification int auto_increment primary key,
    email_to_verify varchar(120) unique not null,
    code int not null,
    code_assignment timestamp default current_timestamp,
    code_expiration timestamp default (current_timestamp + interval 60 minute),
    foreign key (email_to_verify) references user_account(user_email) on update cascade on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- tabla de lista de tareas
create table if not exists task_list (
	id_task_list int auto_increment primary key,
    task varchar(255) not null,
    task_status boolean default false,
    task_creation_date timestamp default current_timestamp,
    task_completion_date timestamp,
    id_user_account int not null,
    foreign key (id_user_account) references user_account(id_user_account) on update cascade on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- tabla de detalle de lista de tareas
create table if not exists task_detail (
	id_task_detail int auto_increment primary key,
    id_task_list int not null,
    task_description varchar(255) not null,
    update_date timestamp,
    id_user_account_update int not null,
    foreign key (id_task_list) references task_list(id_task_list) on update cascade on delete cascade,
    foreign key (id_user_account_update) references user_account(id_user_account) on update cascade on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- tabla de tareas compartidas
 create table if not exists share_task (
	id_share_task int auto_increment primary key,
    id_task_list int not null,
    id_user_account int not null,
    id_share_with_user_account int not null,
    permision_to_edit boolean not null,
    task_sharing_date timestamp default current_timestamp,
    foreign key (id_task_list) references task_list(id_task_list) on update cascade on delete cascade,
    foreign key (id_user_account) references user_account(id_user_account) on update cascade on delete cascade,
    foreign key (id_share_with_user_account) references user_account(id_user_account) on update cascade on delete cascade
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;


 -- creación de los indices de la tabla


 -- insert de información
 insert into privilege (privilege, description_privilege)
values 
("administrador", "usuario administrador del sistema"),
("estandar", "ususario que usa el sistema");