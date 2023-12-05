drop table activityEvent CASCADE CONSTRAINTS;
 drop table perform CASCADE CONSTRAINTS;
 drop table activityType CASCADE CONSTRAINTS;
 drop table reward CASCADE CONSTRAINTS;
 drop table users CASCADE CONSTRAINTS;
 drop table postalCode_City CASCADE CONSTRAINTS;
 drop table community CASCADE CONSTRAINTS;
 drop table memberCount_level CASCADE CONSTRAINTS;
 drop table goal CASCADE CONSTRAINTS;
 drop table suggestion CASCADE CONSTRAINTS;
 drop table mentor CASCADE CONSTRAINTS;
 drop table attending_workshop CASCADE CONSTRAINTS;
 drop table workshop CASCADE CONSTRAINTS;
 drop table courseType_cost CASCADE CONSTRAINTS;


CREATE TABLE activityType
    (activityID NUMBER GENERATED AS IDENTITY,
    typeName VARCHAR(10) NOT NULL,
    rewardRate NUMBER NOT NULL,
    cardioType VARCHAR(50),
    trainingType VARCHAR(50),
    primary key(activityID));
    
CREATE TABLE postalCode_City(
    city VARCHAR(100),
    postalCode VARCHAR(100) PRIMARY KEY);
 
  CREATE TABLE memberCount_level(
   memberCount NUMBER GENERATED AS IDENTITY PRIMARY KEY,
   communityLevel NUMBER DEFAULT 1);

   
 CREATE TABLE community(
   communityID NUMBER GENERATED AS IDENTITY PRIMARY KEY,
   name VARCHAR(50) UNIQUE NOT NULL,
   leader VARCHAR(20) UNIQUE,
   memberCount NUMBER,
   FOREIGN KEY (memberCount) REFERENCES memberCount_level(memberCount)
 );
 
    
    
CREATE TABLE users(
    userID NUMBER GENERATED AS IDENTITY PRIMARY KEY,
    userName VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(100),
    phoneNumber VARCHAR(20),
    address VARCHAR(100),
    totalRewardPoints NUMBER DEFAULT 0,
    postalCode VARCHAR(100),
    partof_community NUMBER,
    FOREIGN KEY (postalCode) REFERENCES postalCode_City(postalCode),
    FOREIGN KEY (partof_community) REFERENCES community(communityID));

CREATE TABLE perform(
    userID NUMBER,
    activityID NUMBER,
    PRIMARY KEY(userID, activityID),
    FOREIGN KEY (userID) REFERENCES users(userID),
    FOREIGN KEY (activityID) REFERENCES activityType(activityID));


 CREATE TABLE reward(
   rewardID NUMBER GENERATED AS IDENTITY PRIMARY KEY,
   rewardPoint NUMBER,
   for_userID NUMBER NOT NULL,
   FOREIGN KEY (for_userID) REFERENCES users(userID));
   
    
 CREATE TABLE activityEvent(
    datetime TIMESTAMP(6),
    description VARCHAR(100),
    duration INTERVAL DAY TO SECOND,
    userID NUMBER,
    activityID NUMBER,
    get_rewardID NUMBER NOT NULL UNIQUE,
    PRIMARY KEY(userID, activityID, datetime),
    FOREIGN KEY (activityID) REFERENCES activityType(activityID),
    FOREIGN KEY (userID) REFERENCES users(userID),
    FOREIGN KEY (get_rewardID) REFERENCES reward(rewardID) ON DELETE CASCADE);
   
 CREATE TABLE goal(
   goalID NUMBER GENERATED AS IDENTITY PRIMARY KEY,
   description VARCHAR(100),
   from_userID NUMBER NOT NULL,
   activityID NUMBER NOT NULL,
   FOREIGN KEY (activityID) REFERENCES activityType(activityID),
   FOREIGN KEY (from_userID) REFERENCES users(userID));
   
  CREATE TABLE mentor(
   mentorID NUMBER GENERATED AS IDENTITY PRIMARY KEY,
   name VARCHAR(50) UNIQUE,
   description VARCHAR(100));
   
 CREATE TABLE suggestion(
   suggestionID NUMBER GENERATED AS IDENTITY PRIMARY KEY,
   from_mentorID NUMBER NOT NULL,
   for_userID NUMBER NOT NULL,
   datetime TIMESTAMP(6) NOT NULL,
   description VARCHAR(200) NOT NULL,
   FOREIGN KEY (for_userID) REFERENCES users(userID),
   FOREIGN KEY (from_mentorID) REFERENCES mentor(mentorID));
   
 CREATE TABLE courseType_cost(
   courseType VARCHAR(20) PRIMARY KEY,
   cost NUMBER NOT NULL);
   
 CREATE TABLE workshop(
   workshopID NUMBER GENERATED AS IDENTITY PRIMARY KEY,
   host NUMBER NOT NULL,
   description VARCHAR(100),
   meetingLink VARCHAR(50) NOT NULL UNIQUE,
   courseType VARCHAR(20),
   name VARCHAR(50) NOT NULL,
   datetime DATE NOT NULL,
   startTime TIMESTAMP(6) NOT NULL,
   endTime TIMESTAMP(6) NOT NULL,
   FOREIGN KEY (host) REFERENCES mentor(mentorID),
   FOREIGN KEY (courseType) REFERENCES courseType_cost(courseType));
   

   
CREATE TABLE attending_workshop(
   workshopID NUMBER,
   from_userID NUMBER,
   PRIMARY KEY(workshopID, from_userID),
   FOREIGN KEY (from_userID) REFERENCES users(userID),
   FOREIGN KEY (workshopID) REFERENCES workshop(workshopID));
   


 
INSERT INTO activityType (typeName, rewardRate, cardioType, TrainingType) 
VALUES ('Cardio', 3.5, 'Running', NULL);
INSERT INTO activityType (typeName, rewardRate, cardioType, TrainingType) 
VALUES ('Cardio', 4.0, 'Cycling', NULL);
INSERT INTO activityType (typeName, rewardRate, cardioType, TrainingType) 
VALUES ('Training', 3.0, NULL, 'Weightlifting');
INSERT INTO activityType (typeName, rewardRate, cardioType, TrainingType) 
VALUES ('Training', 3.5, NULL, 'Bodyweight');
INSERT INTO activityType (typeName, rewardRate, cardioType, TrainingType) 
VALUES ('Cardio', 3.0, 'Jogging', NULL);
INSERT INTO activityType (typeName, rewardRate, cardioType, TrainingType) 
VALUES ('workshop', 3.0, 'Yoga', NULL);
INSERT INTO activityType (typeName, rewardRate, cardioType, TrainingType) 
VALUES ('sports', 3.0, 'basketball', NULL);

INSERT INTO postalCode_City (city, postalCode)
VALUES ('Vancouver', 'V6T 1Z4');
INSERT INTO postalCode_City (city, postalCode)
VALUES ('Vancouver', 'V5L 4S1');
INSERT INTO postalCode_City (city, postalCode)
VALUES ('Baff', 'T1L 1K8');
INSERT INTO postalCode_City (city, postalCode)
VALUES ('Calgary', 'T3S 23J');
INSERT INTO postalCode_City (city, postalCode)
VALUES ('Richmond', 'V6X 174');


INSERT INTO memberCount_level (communityLevel) VALUES (0);
INSERT INTO memberCount_level (communityLevel) VALUES (1);
INSERT INTO memberCount_level (communityLevel) VALUES (1);
INSERT INTO memberCount_level (communityLevel) VALUES (2);
INSERT INTO memberCount_level (communityLevel) VALUES (2);
INSERT INTO memberCount_level (communityLevel) VALUES (2);
INSERT INTO memberCount_level (communityLevel) VALUES (3);
INSERT INTO memberCount_level (communityLevel) VALUES (3);
INSERT INTO memberCount_level (communityLevel) VALUES (3);
INSERT INTO memberCount_level (communityLevel) VALUES (3);
INSERT INTO memberCount_level (communityLevel) VALUES (4);
INSERT INTO memberCount_level (communityLevel) VALUES (4);
INSERT INTO memberCount_level (communityLevel) VALUES (4);
INSERT INTO memberCount_level (communityLevel) VALUES (4);
INSERT INTO memberCount_level (communityLevel) VALUES (4);
INSERT INTO memberCount_level (communityLevel) VALUES (5);
INSERT INTO memberCount_level (communityLevel) VALUES (5);
INSERT INTO memberCount_level (communityLevel) VALUES (5);
INSERT INTO memberCount_level (communityLevel) VALUES (5);
INSERT INTO memberCount_level (communityLevel) VALUES (5);
INSERT INTO memberCount_level (communityLevel) VALUES (5);
INSERT INTO memberCount_level (communityLevel) VALUES (6);
INSERT INTO memberCount_level (communityLevel) VALUES (6);
INSERT INTO memberCount_level (communityLevel) VALUES (6);


INSERT INTO community (name, leader, memberCount) VALUES ('WeightWarriors', 'user1', 1);
INSERT INTO community (name, leader, memberCount) VALUES ('YogaPrincesses', 'user2', 1);
INSERT INTO community (name, leader, memberCount) VALUES ('PilatesPeeps', 'user3', 2);
INSERT INTO community (name, leader, memberCount) VALUES ('RunnersRealm', 'user4', 1);
INSERT INTO community (name, leader, memberCount) VALUES ('CyclistsCircle', 'user5', 1);

INSERT INTO users(userName, password, phoneNumber, address, postalCode, totalRewardPoints)
VALUES ('momo', 'password', '123-456-7890', '5955 Student Union Blvd', 'V6T 1Z4', 10);
INSERT INTO users(userName, password, phoneNumber, address, postalCode, totalRewardPoints)
VALUES ('uann', 'qwertyuiop', '374-293-593', '7781 Professor Rd', 'V5L 4S1', 29);
INSERT INTO users(userName, password, phoneNumber, address, postalCode)
VALUES ('muhammad', 'Is9f#(4*/fs*2sd3', '783-485-382', '8943 West Broad Rd', 'T1L 1K8');
INSERT INTO users(userName, password, phoneNumber, address, postalCode, totalRewardPoints)
VALUES ('howareyou', 'howareyou', NULL, '1111 Unhappy Dr', 'T3S 23J', 20);
INSERT INTO users(userName, password, phoneNumber, address, postalCode, totalRewardPoints)
VALUES ('prayForMidterm', 'ifailed:(', '123-456-7891', '304 DataBase Blvd', NULL, 30);
INSERT INTO users(userName, password, phoneNumber, address, postalCode, totalRewardPoints)
VALUES ('testing purpose','testing','000-000-000', 'heaven st', NULL, 10);
INSERT INTO users(userName, password, phoneNumber, address, postalCode, partof_community, totalRewardPoints)
VALUES ('user1', 'password1', '123-456-7891', '123 Main St', 'V6T 1Z4', 1, 10);
INSERT INTO users(userName, password, phoneNumber, address, postalCode, partof_community, totalRewardPoints)
VALUES ('user2', 'password2', '123-456-7892', '456 Oak St', 'V5L 4S1', 2, 20);
INSERT INTO users(userName, password, phoneNumber, address, postalCode, partof_community, totalRewardPoints)
VALUES ('user3', 'password3', '123-456-7893', '789 Pine St', 'T1L 1K8', 3, 20);
INSERT INTO users(userName, password, phoneNumber, address, postalCode, partof_community, totalRewardPoints)
VALUES ('user4', 'password4', '123-456-7894', '101 Maple Ave', NULL, 4, 30);
INSERT INTO users(userName, password, phoneNumber, address, postalCode, partof_community, totalRewardPoints)
VALUES ('user5', 'password5', '123-456-7895', '202 Birch Rd', NULL, 5, 30);

INSERT INTO perform(userID, activityID)
VALUES (1, 1);
INSERT INTO perform(userID, activityID)
VALUES (1, 2);
INSERT INTO perform(userID, activityID)
VALUES (3, 4);
INSERT INTO perform(userID, activityID)
VALUES (2, 3);
INSERT INTO perform(userID, activityID)
VALUES (5, 5);

INSERT INTO reward(rewardPoint, for_userID)
VALUES (15, 1);
INSERT INTO reward(rewardPoint, for_userID)
VALUES (4*120, 1);
INSERT INTO reward(rewardPoint, for_userID)
VALUES (3*40, 3);
INSERT INTO reward(rewardPoint, for_userID)
VALUES (3*100, 2);
INSERT INTO reward(rewardPoint, for_userID)
VALUES (20*3, 1);


INSERT INTO activityEvent (datetime, description, duration, userID, activityID, get_rewardID)
VALUES (TIMESTAMP '2023-10-12 08:30:00', 'Running from ICCS to Math building..', INTERVAL '00 00:10:00' DAY TO SECOND, 1, 1, 1);
INSERT INTO activityEvent (datetime, description, duration, userID, activityID, get_rewardID)
VALUES (TIMESTAMP '2023-02-01 13:30:00', 'Learned how to ride a bike. So hard.', INTERVAL '00 02:00:00' DAY TO SECOND, 1, 2, 2);
INSERT INTO activityEvent (datetime, description, duration, userID, activityID, get_rewardID)
VALUES (TIMESTAMP '2022-07-08 12:00:00', 'Starting my day with body weight. ', INTERVAL '00 0:40:00' DAY TO SECOND, 3, 4, 3);
INSERT INTO activityEvent (datetime, description, duration, userID, activityID, get_rewardID)
VALUES (TIMESTAMP '2023-11-08 18:40:00', 'Workout after class...', INTERVAL '00 1:40:00' DAY TO SECOND, 2, 3, 4);
INSERT INTO activityEvent (datetime, description, duration, userID, activityID, get_rewardID)
VALUES (TIMESTAMP '2022-04-08 6:30:00', 'Morning Jogging. ', INTERVAL '00 0:20:00' DAY TO SECOND, 5, 5, 5);


INSERT INTO mentor(name, description)
VALUES ('HAi Li', 'She is for sure a super hero. ');
INSERT INTO mentor(name, description)
VALUES ('Bada Lee', 'Dancing queen.');
INSERT INTO mentor(name, description)
VALUES ('Dwayne Johnson', ' He is not only one of the highest-paid actors in the world');
INSERT INTO mentor(name, description)
VALUES ('Arnold Schwarzenegger', 'The fitness legend came out of retirement compete for the Mr. Olympia title.');
INSERT INTO mentor(name, description)
VALUES ('Bas Lil', 'She knows how to convert energy into magic. ');


INSERT INTO goal (description, from_userID, activityID) VALUES ('Complete 5km run', 3, 1);
INSERT INTO goal (description, from_userID, activityID) VALUES ('50kg weight!', 2, 3);
INSERT INTO goal (description, from_userID, activityID) VALUES ('Bike as one of my transportation type', 1, 2);
INSERT INTO goal (description, from_userID, activityID) VALUES ('cultivate habit.', 3, 2);
INSERT INTO goal (description, from_userID, activityID) VALUES ('why do we need a goal??', 5, 1);




INSERT INTO suggestion (from_mentorID, for_userID, datetime, description)
VALUES (1, 1, TIMESTAMP '2023-10-12 14:00:00', 'Consider adding more cardio to your routine.');
INSERT INTO suggestion (from_mentorID, for_userID, datetime, description)
VALUES (2, 2, TIMESTAMP'2023-10-12 15:00:00', 'Weight training can enhance muscle growth.');
INSERT INTO suggestion (from_mentorID, for_userID, datetime, description)
VALUES (3, 3, TIMESTAMP'2023-10-12 16:00:00', 'Yoga can help in relaxation.');
INSERT INTO suggestion (from_mentorID, for_userID, datetime, description)
VALUES (4, 3, TIMESTAMP'2023-10-12 17:00:00', 'Cycling is effective for leg muscles.');
INSERT INTO suggestion (from_mentorID, for_userID, datetime, description)
VALUES (5, 2, TIMESTAMP'2023-10-12 18:00:00', 'Consider joining our new workshop.');

INSERT INTO courseType_cost (courseType, cost) VALUES ('Cardio', 50.0);
INSERT INTO courseType_cost (courseType, cost) VALUES ('Weightlifting', 60.0);
INSERT INTO courseType_cost (courseType, cost) VALUES ('Yoga', 40.0);
INSERT INTO courseType_cost (courseType, cost) VALUES ('Cycling', 45.0);
INSERT INTO courseType_cost (courseType, cost) VALUES ('Pilates', 55.0);



INSERT INTO workshop (host, description, meetingLink, courseType, name, datetime, startTime, endTime) 
VALUES (1, 'Yoga for Beginners', 'https://meetinglink.com/yoga101', 'Yoga', 'Morning Yoga Session', DATE '2023-11-10', TIMESTAMP '2023-11-10 08:00:00', TIMESTAMP '2023-11-10 09:30:00');

INSERT INTO workshop (host, description, meetingLink, courseType, name, datetime, startTime, endTime) 
VALUES (2, 'Advanced Cardio Workout', 'https://meetinglink.com/cardioadvanced', 'Cardio', 'Cardio Blast', DATE '2023-11-11', TIMESTAMP '2023-11-11 10:00:00', TIMESTAMP '2023-11-11 11:00:00');

INSERT INTO workshop (host, description, meetingLink, courseType, name, datetime, startTime, endTime) 
VALUES (3, 'Weightlifting Techniques', 'https://meetinglink.com/weightlift', 'Weightlifting', 'Lift Like a Pro', DATE '2023-11-12', TIMESTAMP '2023-11-12 15:00:00', TIMESTAMP '2023-11-12 16:30:00');

INSERT INTO workshop (host, description, meetingLink, courseType, name, datetime, startTime, endTime) 
VALUES (4, 'Pilates for a Strong Core', 'https://meetinglink.com/pilatescore', 'Pilates', 'Core Pilates', DATE '2023-11-13', TIMESTAMP '2023-11-13 18:00:00', TIMESTAMP '2023-11-13 19:00:00');

INSERT INTO workshop (host, description, meetingLink, courseType, name, datetime, startTime, endTime) 
VALUES (5, 'Mindfulness and Meditation', 'https://meetinglink.com/Cycling', 'Cycling', 'Get Cycling', DATE '2023-11-14', TIMESTAMP '2023-11-14 20:30:00', TIMESTAMP '2023-11-14 21:30:00');





INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 1);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 2);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 3);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 4);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 5);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 6);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 7);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 8);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 9);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 10);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (1, 11);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (4, 1);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (3, 2);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (5, 4);
INSERT INTO attending_workshop(workshopID, from_userID)
VALUES (2, 3);



