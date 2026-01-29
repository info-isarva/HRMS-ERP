-- Run this SQL to add the full_amount_deduct_from_ctc field to employee_statutory_components table
ALTER TABLE `employee_statutory_components` 
ADD COLUMN `full_amount_deduct_from_ctc` BOOLEAN DEFAULT FALSE 
AFTER `epf_option` 
COMMENT 'When true, deducts both employee and employer EPF portions (24% total) from employee CTC';