-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 09, 2026 at 07:00 PM
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
-- Database: `smart_hostel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocations`
--

CREATE TABLE `allocations` (
  `allocation_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('Active','Terminated') DEFAULT 'Active',
  `allocated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allocations`
--

INSERT INTO `allocations` (`allocation_id`, `room_id`, `user_id`, `status`, `allocated_at`) VALUES
(1, 1, 2, 'Active', '2026-11-09 12:55:45'),
(2, 1, 3, 'Active', '2026-11-09 12:55:45'),
(3, 2, 4, 'Active', '2026-11-09 12:55:45'),
(4, 2, 5, 'Active', '2026-11-09 12:55:45'),
(5, 3, 6, 'Active', '2026-11-09 12:55:45'),
(6, 3, 7, 'Active', '2026-11-09 12:55:45'),
(7, 3, 8, 'Active', '2026-11-09 12:55:45'),
(8, 4, 9, 'Active', '2026-11-09 12:55:45'),
(9, 5, 10, 'Active', '2026-11-09 12:55:45'),
(10, 5, 11, 'Active', '2026-11-09 12:55:45'),
(11, 6, 12, 'Active', '2026-11-09 12:55:45'),
(12, 7, 13, 'Active', '2026-11-09 12:55:45'),
(13, 7, 14, 'Active', '2026-11-09 12:55:45'),
(14, 8, 15, 'Active', '2026-11-09 12:55:45'),
(15, 8, 16, 'Active', '2026-11-09 12:55:45'),
(16, 8, 17, 'Active', '2026-11-09 12:55:45'),
(17, 9, 18, 'Active', '2026-11-09 12:55:45'),
(18, 9, 19, 'Active', '2026-11-09 12:55:45'),
(19, 10, 20, 'Active', '2026-11-09 12:55:45'),
(20, 10, 21, 'Active', '2026-11-09 12:55:45'),
(21, 11, 22, 'Active', '2026-11-09 12:55:45'),
(22, 12, 23, 'Active', '2026-11-09 12:55:45'),
(23, 12, 24, 'Active', '2026-11-09 12:55:45'),
(24, 13, 25, 'Active', '2026-11-09 12:55:45'),
(25, 15, 26, 'Active', '2026-11-09 13:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Pending','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `user_id`, `title`, `description`, `status`, `created_at`) VALUES
(1, 2, 'Wi-Fi Speed Slow', 'Internet connection on the 2nd floor is very slow at night.', 'Pending', '2026-11-09 12:55:45'),
(2, 4, 'Ceiling Fan Noise', 'The ceiling fan in room 104 makes a loud sound.', 'Pending', '2026-11-09 12:55:45'),
(3, 7, 'Water Tap Leakage', 'Bathroom tap is leaking continuously.', 'Pending', '2026-11-09 12:55:45'),
(4, 10, 'AC Cooling Issue', 'Room 201 AC is not cooling properly.', 'Pending', '2026-11-09 12:55:45'),
(5, 15, 'Study Table Broken', 'One leg of the study table needs repair.', 'Resolved', '2026-11-09 12:55:45'),
(6, 3, 'Bathroom Water Tap Leakage & Low Pressure', 'The water tap inside our attached bathroom has been leaking continuously since last night, and the water pressure is very low. Please send a plumber to fix this issue as soon as possible.', 'Pending', '2026-11-09 17:33:39');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `facility_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Available','Not Available') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `hostel_id` int(11) NOT NULL,
  `hostel_name` varchar(100) NOT NULL,
  `type` enum('Boys','Girls','Co-Ed') NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`hostel_id`, `hostel_name`, `type`, `address`, `created_at`) VALUES
(1, 'North Hall', 'Boys', 'Dhaka', '2026-10-02 16:32:18'),
(2, 'South Hall', 'Girls', 'Dhaka', '2026-10-02 16:32:18'),
(3, 'East Wing Hall', 'Boys', 'Block C, Dhanmondi, Dhaka', '2026-11-09 12:55:45'),
(4, 'West Paradise Hall', 'Girls', 'Block D, Uttara, Dhaka', '2026-11-09 12:55:45'),
(5, 'Central International Hostel', 'Co-Ed', 'Mirpur 10, Dhaka', '2026-11-09 12:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `notice_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`notice_id`, `title`, `content`, `created_at`) VALUES
(1, 'Monthly Hostel Rent & Dining Fee Payment Notice', 'All resident students are hereby notified that the monthly room rent and dining fees for the current month must be cleared by the 10th of this month. Please pay your dues on time to avoid any late fine. Contact the hostel office for any payment-related queries.', '2026-11-09 16:49:50');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `amount`, `payment_date`, `status`) VALUES
(1, 2, 5000.00, '2026-11-01', 'Paid'),
(2, 3, 4500.00, '2026-11-02', 'Paid'),
(3, 4, 8000.00, '2026-11-05', 'Pending'),
(4, 5, 4500.00, '2026-11-08', 'Pending'),
(5, 26, 5000.00, '2026-11-09', 'Paid'),
(6, 9, 1000.00, '2026-11-09', 'Paid'),
(7, 18, 5000.00, '2026-11-09', 'Paid'),
(8, 3, 500.00, '2026-11-09', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL DEFAULT 1,
  `room_number` varchar(50) NOT NULL,
  `room_type` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `price_per_month` decimal(10,2) NOT NULL,
  `status` enum('Available','Full','Maintenance') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `hostel_id`, `room_number`, `room_type`, `capacity`, `price_per_month`, `status`) VALUES
(1, 1, '1', 'Single AC', 1, 1.00, 'Available'),
(2, 1, '102', 'Double Non-AC', 2, 4500.00, 'Available'),
(3, 1, '103', '4-Bed Share', 4, 3000.00, 'Available'),
(4, 1, '104', 'Single AC', 1, 8000.00, 'Available'),
(5, 2, '105', 'Double Non-AC', 2, 4500.00, 'Available'),
(6, 2, '201', 'Single AC', 1, 8500.00, 'Available'),
(7, 2, '202', 'Double Non-AC', 2, 5000.00, 'Available'),
(8, 2, '203', '4-Bed Share', 4, 3200.00, 'Available'),
(9, 3, '204', '4-Bed Share', 4, 3200.00, 'Available'),
(10, 3, '205', 'Double Non-AC', 2, 4800.00, 'Available'),
(11, 3, '301', 'Single AC', 1, 9000.00, 'Available'),
(12, 4, '302', 'Double Non-AC', 2, 5000.00, 'Available'),
(13, 4, '303', '4-Bed Share', 4, 3500.00, 'Available'),
(14, 4, '304', '4-Bed Share', 4, 3500.00, 'Available'),
(15, 5, '305', 'Double Non-AC', 2, 4500.00, 'Available'),
(16, 5, '401', 'Single AC', 1, 8000.00, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `room_applications`
--

CREATE TABLE `room_applications` (
  `application_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `room_type` varchar(100) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_applications`
--

INSERT INTO `room_applications` (`application_id`, `user_id`, `hostel_id`, `room_type`, `status`, `applied_at`) VALUES
(1, 21, 1, 'Single AC', 'Pending', '2026-11-09 16:54:58'),
(2, 22, 2, 'Double Non-AC', 'Pending', '2026-11-09 16:54:58'),
(3, 2, 1, '4-Bed Share', 'Approved', '2026-11-09 16:54:58'),
(4, 3, 4, '4-Bed Share', 'Pending', '2026-11-09 17:28:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Staff','Customer') NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, 'admin@gmail.com', 'admin123', 'Admin', 'Active', '2026-10-02 15:59:18'),
(2, 'samia@gmail.com', 'samia123', 'Customer', 'Active', '2026-10-02 15:59:18'),
(3, 'puspo34@gmail.com', 'puspo2270', 'Customer', 'Active', '2026-11-06 18:00:00'),
(4, 'ariful.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(5, 'nusrat.jahan@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(6, 'tanvir.ahmed@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(7, 'sadia.afrin@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(8, 'mehedi.hasan@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(9, 'farhana.akter@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(10, 'rakibul.alam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(11, 'sumaiya.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(12, 'rafiqul.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(13, 'jannatul.ferdous@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(14, 'shakil.ahmed@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(15, 'tania.sultana@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(16, 'mahmudul.hasan@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(17, 'tasnim.tabassum@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(18, 'fahim.faisal@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(19, 'mim.akter@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(20, 'sabbir.hossain@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(21, 'sharmin.nahar@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(22, 'naimur.rahman@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(23, 'ayesha.siddika@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(24, 'ashraful.alam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(25, 'fatema.tuz@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(26, 'tariqul.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(27, 'marufa.begum@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(28, 'zahid.hasan@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(29, 'nabila.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(30, 'kamrul.hasan@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(31, 'rubaiya.rahman@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(32, 'shafiqul.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(33, 'sanjida.akter@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(34, 'mizanur.rahman@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(35, 'khaleda.akter@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(36, 'monirul.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(37, 'rifat.jahan@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(38, 'saiful.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(39, 'habiba.sultana@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(40, 'anwar.hossain@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(41, 'roksana.parvin@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(42, 'habibur.rahman@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(43, 'sonia.akter@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(44, 'delwar.hossain@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(45, 'nasrin.akter@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(46, 'touhidul.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(47, 'sabina.yasmin@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(48, 'jahangir.alam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(49, 'salma.begum@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(50, 'mostafizur.rahman@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(51, 'reshma.akter@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(52, 'shahadat.hossain@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(53, 'monira.begum@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(54, 'abul.kalam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(55, 'razib.ahmed@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(56, 'naznin.nahar@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(57, 'firoz.ahmed@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(58, 'shirin.akter@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(59, 'masud.rana@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(60, 'dilruba.yasmin@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45'),
(61, 'aminul.islam@gmail.com', 'pass123', 'Customer', 'Active', '2026-11-09 12:55:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocations`
--
ALTER TABLE `allocations`
  ADD PRIMARY KEY (`allocation_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`facility_id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`hostel_id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`notice_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `unique_hostel_room` (`hostel_id`,`room_number`);

--
-- Indexes for table `room_applications`
--
ALTER TABLE `room_applications`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocations`
--
ALTER TABLE `allocations`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `facility_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `hostel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `notice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `room_applications`
--
ALTER TABLE `room_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
