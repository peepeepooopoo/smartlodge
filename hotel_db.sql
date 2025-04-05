-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2025 at 06:13 PM
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
-- Database: `hotel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BookingID` int(11) NOT NULL,
  `GuestID` int(11) DEFAULT NULL,
  `RoomNumber` int(11) DEFAULT NULL,
  `CheckInDate` date NOT NULL,
  `CheckOutDate` date NOT NULL,
  `TotalPrice` decimal(10,2) DEFAULT NULL CHECK (`TotalPrice` >= 0),
  `Capacity` int(11) DEFAULT NULL,
  `Status` enum('pending_payment','approved','completed','cancelled') NOT NULL DEFAULT 'pending_payment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BookingID`, `GuestID`, `RoomNumber`, `CheckInDate`, `CheckOutDate`, `TotalPrice`, `Capacity`, `Status`) VALUES
(4, 20, 201, '2025-04-02', '2025-04-04', 300.00, 2, 'pending_payment'),
(5, 26, 101, '2025-04-04', '2025-04-11', 700.00, 2, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `guest`
--

CREATE TABLE `guest` (
  `GuestID` int(11) NOT NULL,
  `FullName` varchar(50) NOT NULL,
  `DateOfBirth` date NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest`
--

INSERT INTO `guest` (`GuestID`, `FullName`, `DateOfBirth`, `Address`, `Phone`, `Email`) VALUES
(20, 'guest', '2025-04-02', 'guest', '4235643567', 'guest@gmail.com'),
(21, 'guest1', '2025-04-05', 'guest1', '11111111111', 'guest1@gmail.com'),
(24, 'guest6', '2025-04-05', 'guest6', '11111111116', 'guest6@gmail.com'),
(25, 'zuala', '1980-02-21', 'zuala', '8888888888', 'zuala@gmail.com'),
(26, 'opa3', '2002-03-21', 'opa3', '2345332127', 'opa3@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `hotel`
--

CREATE TABLE `hotel` (
  `HotelID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Stars` int(11) DEFAULT NULL CHECK (`Stars` between 1 and 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel`
--

INSERT INTO `hotel` (`HotelID`, `Name`, `Address`, `Phone`, `Email`, `Stars`) VALUES
(1, 'Smartlodge', 'Goa, India', '+91 9876543210', 'info@smartlodge.com', 5);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `BookingID` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) NOT NULL CHECK (`Amount` > 0),
  `PaymentDate` date NOT NULL,
  `PaymentMethod` varchar(15) NOT NULL CHECK (`PaymentMethod` in ('Credit Card','Debit Card','Cash','UPI'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `BookingID`, `Amount`, `PaymentDate`, `PaymentMethod`) VALUES
(2, 4, 300.00, '2025-04-01', 'Credit Card'),
(3, 5, 700.00, '2025-04-01', 'Credit Card');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `RoomNumber` int(11) NOT NULL,
  `HotelID` int(11) DEFAULT NULL,
  `TypeID` int(11) DEFAULT NULL,
  `Status` enum('Available','Booked') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`RoomNumber`, `HotelID`, `TypeID`, `Status`) VALUES
(101, 1, 2, 'Booked'),
(102, 1, 2, 'Available'),
(103, 1, 2, 'Available'),
(104, 1, 2, 'Available'),
(105, 1, 2, 'Available'),
(106, 1, 2, 'Available'),
(107, 1, 2, 'Available'),
(108, 1, 2, 'Available'),
(109, 1, 2, 'Available'),
(110, 1, 2, 'Available'),
(201, 1, 1, 'Available'),
(202, 1, 1, 'Available'),
(203, 1, 1, 'Available'),
(204, 1, 1, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `roomtype`
--

CREATE TABLE `roomtype` (
  `TypeID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `PricePerNight` decimal(10,2) NOT NULL CHECK (`PricePerNight` > 0),
  `Capacity` int(11) NOT NULL CHECK (`Capacity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roomtype`
--

INSERT INTO `roomtype` (`TypeID`, `Name`, `Description`, `PricePerNight`, `Capacity`) VALUES
(1, 'Deluxe', 'Spacious room with premium amenities', 150.00, 4),
(2, 'Standard', 'Comfortable room with basic facilities', 100.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `HotelID` int(11) DEFAULT NULL,
  `Position` varchar(100) NOT NULL,
  `Salary` decimal(10,2) NOT NULL CHECK (`Salary` > 0),
  `DOB` date NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `HireDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guest','staff','admin') NOT NULL,
  `country` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `country`, `created_at`) VALUES
(16, 'admin', 'admin@gmail.com', '$2y$10$9x5tXlPk77.HMvkbVuW1ue0PB5wnM1gJOdlXpjua82fqPypUN5eFW', 'admin', 'BR', '2025-03-31 20:43:49'),
(20, 'guest', 'guest@gmail.com', '$2y$10$Ydi1CD4i.8iN/xDLsP5r9ugZY/bsw3m.zO9m23AGTAfubIMDf7lcS', 'guest', '', '2025-03-31 21:56:42'),
(21, 'guest1', 'guest1@gmail.com', '$2y$10$3NZLsr9E/O0WU/XnaVQHZOos1tTgTeFqLI7RRFvQSlXUXUzl43piC', 'guest', '', '2025-03-31 23:06:02'),
(24, 'guest6', 'guest6@gmail.com', '$2y$10$PVF/qByN3WUqfplQKuRyp.QxtGK89xzLtE9dUsyPfoDHEh4SyF1p6', 'guest', '', '2025-03-31 23:07:08'),
(25, 'zuala', 'zuala@gmail.com', '$2y$10$eQTvh8J0bGNvOw4hw/zbB.vlpWAYJ8C5jStMaTNgty2j1jUKcG/t2', 'guest', '', '2025-03-31 23:08:38'),
(26, 'opa3', 'opa3@gmail.com', '$2y$10$XQYD97iS.X4idBCjnlUlZOveCIRSjB/dh/xJMtn5OT7bXBb6vYJSq', 'guest', 'AU', '2025-03-31 23:11:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `RoomNumber` (`RoomNumber`),
  ADD KEY `fk_booking_guest` (`GuestID`);

--
-- Indexes for table `guest`
--
ALTER TABLE `guest`
  ADD PRIMARY KEY (`GuestID`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `hotel`
--
ALTER TABLE `hotel`
  ADD PRIMARY KEY (`HotelID`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `BookingID` (`BookingID`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`RoomNumber`),
  ADD KEY `HotelID` (`HotelID`),
  ADD KEY `TypeID` (`TypeID`);

--
-- Indexes for table `roomtype`
--
ALTER TABLE `roomtype`
  ADD PRIMARY KEY (`TypeID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `HotelID` (`HotelID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hotel`
--
ALTER TABLE `hotel`
  MODIFY `HotelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roomtype`
--
ALTER TABLE `roomtype`
  MODIFY `TypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`GuestID`) REFERENCES `guest` (`GuestID`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`RoomNumber`) REFERENCES `rooms` (`RoomNumber`) ON DELETE CASCADE;

--
-- Constraints for table `guest`
--
ALTER TABLE `guest`
  ADD CONSTRAINT `fk_guest_user` FOREIGN KEY (`GuestID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`HotelID`) REFERENCES `hotel` (`HotelID`) ON DELETE CASCADE,
  ADD CONSTRAINT `rooms_ibfk_2` FOREIGN KEY (`TypeID`) REFERENCES `roomtype` (`TypeID`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`HotelID`) REFERENCES `hotel` (`HotelID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
