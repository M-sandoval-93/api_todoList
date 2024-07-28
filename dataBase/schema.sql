CREATE SCHEMA `todoList` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;

create table privilege (
	id_privilege int auto_increment primary key,
    privilege varchar(120) not null
);

create table user (
	id_user int auto_increment primary key,
    name_user varchar(80) not null,
    email_user varchar(200) unique not null,
    password_user varchar(120) not null,
    id_privilege int not null,
    state_user boolean default true,
    first_login timestamp, 
    foreign key (id_privilege) references privilege(id_privilege)
);

create table todo (
	id_todo int auto_increment primary key,
    title_todo varchar(255) not null,
    state_todo boolean default false,
    date_create_todo timestamp default now(),
    date_complete_todo timestamp,
    id_user int not null,
    foreign key (id_user) references user(id_user) on delete cascade
);

create table detail_todo (
	id_detail_todo int auto_increment primary key,
    description_todo varchar(255) not null,
    id_todo int not null,
    foreign key (id_todo) references todo(id_todo) on delete cascade
);

create table share_todo (
	id_share_todo int auto_increment primary key,
    id_todo int not null,
    id_user int not null,
    shared_with_id_user int not null,
    permission_to_edit boolean not null,
    foreign key (id_todo) references todo(id_todo) on delete cascade,
    foreign key (id_user) references user(id_user) on delete cascade,
    foreign key (shared_with_id_user) references user(id_user) on delete cascade
);

-- select normales sobre las tablas
select * from user
select * from privilege
select * from todo
select * from share_todo

-- select del share todo para obtener todos los todo normales y compartidos
select todo.*, share_todo.shared_with_id_user
from todo
left join share_todo on todo.id_todo = share_todo.id_share_todo
where todo.id_user = 2 or share_todo.shared_with_id_user = 2

-- insert de datos
-- ingreso de privilegios 
insert into privilege (privilege) values ('admin');

-- ingreso de usuarios
insert into user (name_user, email_user, password_user, id_privilege) values ('sandoval', 'sandoval@gmail.com', 'sandoval', 1);
insert into user (name_user, email_user, password_user, id_privilege) values ('vasquez', 'vasquez@gmail.com', 'vasquez', 1);

-- ingreso de algunos todo
insert into todo (title_todo, id_user) values ('first todo', 1); 
insert into todo (title_todo, id_user) values ('second todo', 1);
insert into todo (title_todo, id_user) values ('last', 1);

-- compartir algun todo
insert into share_todo (id_todo, id_user, shared_with_id_user, permission_to_edit) values (1, 1, 2, true)
