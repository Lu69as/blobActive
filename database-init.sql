drop table if exists sets_history;
drop table if exists sets;
drop table if exists plan_exercise;
drop table if exists exercises;
drop table if exists plans;
drop table if exists group_users;
drop table if exists groups;

create table groups (
	groupId int primary key not null auto_increment,
	name varchar(50)
);

create table group_users (
	groupUserId int not null auto_increment,
	groupId int,
	userId char(32),
    PRIMARY KEY (groupUserId, groupId, userId),
	foreign key (groupId) references groups(groupId) ON DELETE CASCADE
);

create table plans (
	planId int primary key not null auto_increment,
	groupId int,
	userId char(32),
	name varchar(50),
	weekday int,
	foreign key (groupId) references groups(groupId) ON DELETE CASCADE
);

create table exercises (
	exerciseId int primary key not null auto_increment,
	name varchar(50),
	valueType varchar(50)
);

create table plan_exercise (
	plan_exerciseId int primary key not null auto_increment,
    planId int,
    exerciseId int,
    setsNum int,
    FOREIGN KEY (exerciseId) REFERENCES exercises(exerciseId) ON DELETE CASCADE,
    FOREIGN KEY (planId) REFERENCES plans(planId) ON DELETE CASCADE
);

create table sets (
	setId int primary key not null auto_increment,
	plan_exerciseId int,
	setNote varchar(50),
	setLength int,
	weight varchar(50),
    FOREIGN KEY (plan_exerciseId) REFERENCES plan_exercise(plan_exerciseId) ON DELETE CASCADE
);

create table sets_history (
	plan_exerciseId int,
	setLength int,
	weight varchar(50),
    FOREIGN KEY (plan_exerciseId) REFERENCES plan_exercise(plan_exerciseId) ON DELETE CASCADE
);

insert into groups (name) values ("Relevant"), ('New group');
insert into group_users (groupId, userId) values (1, "lu69as"), (1, "lokas"), (2, "lokas"), (2, "lu69as");

insert into plans (groupId, userId, name, weekday) values (1, "lu69as", "Beindag", 1), (1, "lokas", "Biceps", 7);

insert into exercises (name, valueType) values ("Bicep curls", "Reps"), ("Plank", "Seconds");

insert into plan_exercise (planId, exerciseId, setsNum) values (1, 1, 3), (1, 2, 1);

insert into sets (plan_exerciseId, setLength, weight) values (1, 11, "20kg"), (1, 10, "20kg"), (1, 10, "20kg"), (2, 60, "0kg");
insert into sets_history (plan_exerciseId, setLength, weight) values (1, 12, "17.5kg"), (1, 11, "20kg"), (1, 10, "20kg"), (2, 50, "0kg");