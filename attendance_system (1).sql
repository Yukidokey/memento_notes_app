-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2026 at 04:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendance_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `scanner_field` varchar(100) DEFAULT NULL,
  `c_date` date DEFAULT NULL,
  `c_time` time DEFAULT NULL,
  `t_events` text DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `scan_date` date DEFAULT NULL,
  `scan_time` time DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collegedepartmentform`
--

CREATE TABLE `collegedepartmentform` (
  `id` int(8) NOT NULL,
  `dataOne` varchar(255) NOT NULL,
  `dataTwo` varchar(255) NOT NULL,
  `dataThree` varchar(255) NOT NULL,
  `dataFour` varchar(255) NOT NULL,
  `dataFive` varchar(255) NOT NULL,
  `dataSix` varchar(255) NOT NULL,
  `status_one` enum('Active','Inactive') NOT NULL,
  `dateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `college_id` int(11) NOT NULL,
  `college_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`college_id`, `college_name`) VALUES
(1, 'Renzs'),
(2, 'Renzs'),
(3, 'Renzs'),
(4, ''),
(5, 'Mizuki'),
(6, 'Mizuki'),
(7, 'Mizuki'),
(8, ''),
(10, ''),
(11, 'Daniel');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_description` text DEFAULT NULL,
  `number_of_units` int(11) NOT NULL,
  `course_type` enum('Core','Major','Elective') NOT NULL,
  `course_status` enum('Active','Inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coursesform`
--

CREATE TABLE `coursesform` (
  `id` int(11) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coursesform`
--

INSERT INTO `coursesform` (`id`, `department`, `course_name`) VALUES
(2, 'Information Technology', 'Web Development'),
(3, 'Information Technology', 'Cybersecurity');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `college` varchar(100) NOT NULL,
  `dept_name` varchar(150) NOT NULL,
  `coordinator_fname` varchar(100) NOT NULL,
  `coordinator_mname` varchar(100) DEFAULT NULL,
  `coordinator_lname` varchar(100) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `college`, `dept_name`, `coordinator_fname`, `coordinator_mname`, `coordinator_lname`, `date_created`) VALUES
(1, 'CEAC', 'bsit', 'paul', 'puno', 'barlosca', '2026-03-10 03:27:36'),
(2, 'CEAC', 'BSIT', 'Daniel', 'James', 'Libunao', '2026-03-10 03:29:47');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_type` enum('Academic','Sports','Cultural','Seminar') DEFAULT 'Academic',
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `event_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `studentID` varchar(50) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `middleName` varchar(50) DEFAULT NULL,
  `lastName` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `age` int(11) DEFAULT NULL,
  `day` enum('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31') NOT NULL,
  `month` enum('1','2','3','4','5','6','7','8','9','10','11','12') NOT NULL,
  `year` enum('1926','1927','1928','1929','1930','1931','1932','1933','1934','1935','1936','1937','1938','1939','1940','1941','1942','1943','1944','1945','1946','1947','1948','1949','1950','1951','1952','1953','1954','1955','1956','1957','1958','1959','1960','1961','1962','1963','1964','1965','1966','1967','1968','1969','1970','1971','1972','1973','1974','1975','1976','1977','1978','1979','1980','1981','1982','1983','1984','1985','1986','1987','1988','1989','1990','1991','1992','1993','1994','1995','1996','1997','1998','1999','2000','2001','2002','2003','2004','2005','2006','2007','2008','2009','2010','2011','2012','2013','2014','2015','2016','2017','2018','2019','2020','2021','2022','2023','2024','2025','2026') NOT NULL,
  `course_program` enum('Computer Science','Information Technology','Software Engineering','Data Science','Cybersecurity','Artificial Intelligence','Business Administration','Accounting','Economics','Marketing','Finance','Human Resource Management','Psychology','Sociology','Biology','Chemistry','Physics','Mathematics','Statistics','Environmental Science','Mechanical Engineering','Electrical Engineering','Civil Engineering','Architecture','Graphic Design','Fashion Design','Hospitality Management','Tourism','Education','Nursing','Pharmacy','Law','Political Science','History','Philosophy','Journalism','Mass Communication','Music','Fine Arts','Sports Science') NOT NULL,
  `year_level` enum('1st','2nd','3rd','4th') NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `student_type` enum('Regular','Irregular') NOT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `home_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`studentID`, `firstName`, `middleName`, `lastName`, `gender`, `age`, `day`, `month`, `year`, `course_program`, `year_level`, `section`, `student_type`, `email_address`, `contact_no`, `home_address`) VALUES
('2023625', 'paul', 'puno', 'barlosca', 'Male', 21, '2', '11', '2004', 'Information Technology', '3rd', 'BSIT-3B', 'Regular', 'pbarlosca', '09702722411', 'brgy.morales prk.sueno village'),
('2023626', 'paul', 'puno', 'barlosca', 'Male', 21, '2', '11', '2004', 'Information Technology', '3rd', 'BSIT-3B', 'Regular', 'pbarlosca@gmail.com', '09702722411', 'Brgy.Morales Prk.Sueno Village');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `college_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `first_name`, `middle_name`, `last_name`, `status`, `college_id`) VALUES
(2, 'admin', '1234', 'user', NULL, NULL, NULL, NULL, NULL),
(4, 'jakecyrus', '1234', 'Super Admin', 'jake', 'cyrus', 'malupiton', 'Active', NULL),
(5, 'paul.dean', '12345', 'SUPER ADMIN', 'Paul', 'John', 'Barlosca', 'active', 1),
(6, 'paul.dean', '12345', 'SUPER ADMIN', 'Paul', 'John', 'Barlosca', 'active', 2),
(7, 'paul.dean', '12345', 'SUPER ADMIN', 'Paul', 'John', 'Barlosca', 'active', 3),
(8, 'renzo02', 'Penelope345', 'Super Admin', 'Paul', 'Puno', 'Barlosca', 'Active', NULL),
(9, '.dean', '12345', 'SUPER ADMIN', '', '', '', 'active', 4),
(10, 'Kaizen02', 'iloveyou123', 'Department President', 'Kaizen', 'Ryo', 'Zaraki', 'Inactive', NULL),
(11, 'kaizo.dean', '12345', 'SUPER ADMIN', 'Kaizo', 'Ryu', 'Zenpu', 'active', 5),
(12, 'kaizo.dean', '12345', 'SUPER ADMIN', 'Kaizo', 'Ryu', 'Zenpu', 'active', 6),
(13, 'kaizo.dean', '12345', 'SUPER ADMIN', 'Kaizo', 'Ryu', 'Zenpu', 'active', 7),
(14, '.dean', '12345', 'SUPER ADMIN', '', '', '', 'active', 8),
(15, 'clarenz.dean', '12345', 'SUPER ADMIN', 'Clarenz', 'Dave', 'Rubrico', 'active', 9),
(16, '.dean', '12345', 'SUPER ADMIN', '', '', '', 'active', 10),
(20, 'paul.barlosca', 'password123', 'Program Coordinator', NULL, NULL, NULL, NULL, NULL),
(21, 'daniel.libunao', 'password123', 'Program Coordinator', NULL, NULL, NULL, NULL, NULL),
(22, 'paul.barlosca', 'password123', 'Program Coordinator', NULL, NULL, NULL, NULL, NULL),
(23, 'paul renzs.barlosca', 'password123', 'Program Coordinator', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `yearsectionform`
--

CREATE TABLE `yearsectionform` (
  `year_level` enum('1st','2nd','3rd','4th') NOT NULL,
  `sec_name` varchar(255) NOT NULL,
  `a_year` varchar(255) NOT NULL,
  `a_name` varchar(255) NOT NULL,
  `max_students` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `collegedepartmentform`
--
ALTER TABLE `collegedepartmentform`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`college_id`);

--
-- Indexes for table `coursesform`
--
ALTER TABLE `coursesform`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`studentID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `collegedepartmentform`
--
ALTER TABLE `collegedepartmentform`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `college_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `coursesform`
--
ALTER TABLE `coursesform`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
