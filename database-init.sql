drop table if exists exercises;
drop table if exists plans;
drop table if exists groups_users;
drop table if exists groups;

create table groups (
	groupId int primary key not null auto_increment,
	name varchar(50)
);

create table groups_users (
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
	planHistory mediumtext null,
	foreign key (groupId) references groups(groupId) ON DELETE CASCADE
);

create table exercises (
	exerciseId int primary key not null auto_increment,
	planId int,
	name varchar(50),
	sets int,
	reps varchar(20),
	weight varchar(50) null,
	foreign key (planId) references plans(planId) ON DELETE CASCADE
);