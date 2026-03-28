ALTER  TABLE loan_applications 
ADD COLUMN    lead_id VARCHAR(20) UNIQUE,
ADD COLUMN    name VARCHAR(100),
ADD COLUMN    email VARCHAR(100),
ADD COLUMN    mobile VARCHAR(15),
ADD COLUMN    income_source ENUM('salaried', 'non_salaried'),
ADD COLUMN    income DECIMAL(12,2),
ADD COLUMN    pincode VARCHAR(6),
ADD COLUMN    dob DATE,
ADD COLUMN    pan VARCHAR(10),
ADD COLUMN    aadhaar VARCHAR(12),
ADD COLUMN    loan_amount DECIMAL(12,2),
ADD COLUMN    status ENUM('new', 'provider_selected', 'docs_uploaded', 'completed'),
ADD COLUMN    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE loan_providers_selection 
ADD COLUMN    lead_id VARCHAR(20),
ADD COLUMN    provider_id INT,
ADD COLUMN    selected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

ALTER TABLE loan_providers_selection ADD CONSTRAINT fk_lead_id  FOREIGN KEY (lead_id) REFERENCES loan_applications(lead_id);


ALTER TABLE document_uploads 
ADD COLUMN    lead_id VARCHAR(20),
ADD COLUMN    document_type VARCHAR(50),
ADD COLUMN    s3_path VARCHAR(255),
ADD COLUMN    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;


ALTER TABLE document_uploads ADD CONSTRAINT fk_lead_id  FOREIGN KEY (lead_id) REFERENCES loan_applications(lead_id);



CREATE TRIGGER `generate_loan_id` 

BEFORE INSERT ON `loan_applications` 

FOR EACH ROW 

BEGIN

  SET NEW.lead_id = CONCAT('LOAN', NEW.id)

END;


CREATE TRIGGER generate_loan_id BEFORE INSERT ON loan_applications
       FOR EACH ROW SET @lead_id = CONCAT('LOAN' + NEW.id);
