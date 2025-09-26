drop table if exists workout_sets;
drop table if exists workout_exercise;
drop table if exists workouts;
drop table if exists plan_sets;
drop table if exists plan_exercise;
drop table if exists plans;
drop table if exists exercises;
drop table if exists group_users;
drop table if exists groups;

create table groups (
	groupId int primary key not null auto_increment,
	groupName varchar(50)
);

create table group_users (
	groupUserId int not null auto_increment,
	groupId int,
	userId char(32),
    PRIMARY KEY (groupUserId, groupId, userId),
	foreign key (groupId) references groups(groupId) ON DELETE CASCADE
);

create table exercises (
	exerciseId int primary key not null auto_increment,
	exerciseName varchar(50)
);

create table plans (
	planId int primary key not null auto_increment,
	groupId int,
	userId char(32),
	planName varchar(50),
	weekday int,
	foreign key (groupId) references groups(groupId) ON DELETE CASCADE
);

create table plan_exercise (
	plan_exerciseId int primary key not null auto_increment,
    planId int,
    exerciseId int,
    FOREIGN KEY (exerciseId) REFERENCES exercises(exerciseId) ON DELETE CASCADE,
    FOREIGN KEY (planId) REFERENCES plans(planId) ON DELETE CASCADE
);

create table plan_sets (
	plan_setId int primary key not null auto_increment,
	plan_exerciseId int,
	plan_setNote varchar(50) default "",
	plan_setLength int default "12",
	plan_weight varchar(50) default "0kg",
    FOREIGN KEY (plan_exerciseId) REFERENCES plan_exercise(plan_exerciseId) ON DELETE CASCADE
);

create table workouts (
	workoutId int primary key not null auto_increment,
	planId int,
	workout_date date default CURRENT_TIMESTAMP(),
    FOREIGN KEY (planId) REFERENCES plans(planId) ON DELETE CASCADE
);

create table workout_exercise (
	workout_exerciseId int primary key not null auto_increment,
    workoutId int,
    exerciseId int,
    FOREIGN KEY (workoutId) REFERENCES workouts(workoutId) ON DELETE cascade,
    FOREIGN KEY (exerciseId) REFERENCES exercises(exerciseId) ON DELETE CASCADE
);

create table workout_sets (
	workout_setId int primary key not null auto_increment,
	workout_exerciseId int,
	workout_setNote varchar(50),
	workout_setLength int,
	workout_weight varchar(50),
    FOREIGN KEY (workout_exerciseId) REFERENCES workout_exercise(workout_exerciseId) ON DELETE CASCADE
);

insert into groups (groupName) values ("Relevant"), ('New group');
insert into group_users (groupId, userId) values (1, "gde"), (1, "lokas"), (2, "lokas"), (2, "gde");

insert into plans (groupId, userId, planName, weekday) values (1, "gde", "Beindag", 1), (1, "lokas", "Biceps", 7);

insert into exercises (exerciseName) values 
("dumbell curls"), ("barbell curls"), ("barbell benchpress"), ("dumbell benchpress"), ("dumbell shoulderpress"),
("plank"), ("side plank"), ("goblet squat hold"), ("dead hang"), ("active hang"),
("goblet squats"), ("squats"), ("deadlift"), ("leg extensions"), ("leg curl"), ("push ups");

insert into plan_exercise (planId, exerciseId) values (1, 1), (1, 2);

insert into plan_sets (plan_exerciseId, plan_setNote, plan_setLength, plan_weight) values 
	(1, "Need more kilos", 11, "20kg"), (1, "", 10, "20kg"), (1, "", 10, "20kg"), (2, "", 60, "0kg");
insert into plan_sets (plan_exerciseId) values (2);




