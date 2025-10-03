Setup:
## php artisan install:api 
# php artisan passport:install
# php artisan passport:client --personal
# php artisan migrate --path=/database/migrations/ (remove all oauth-related migrations)
# php artisan db:seed --class=BarangaySeeder OR php artisan db:seed
# php artisan db:seed --class=ResidentsSeeder OR php artisan db:seed

#this willl create a new model with controller & migration
# php artisan make:model {Resident} -c -m 

#Create interface
# php artisan make:interface ResidentRepositoryInterface

#Create a service provider instance
# php artisan make:provider {ResidentServiceProvider}

#Create Request Class
# php artisan make:request StoreResidentRequest
# php artisan make:request UpdateResidentRequest

#Create a resource provider instance
# php artisan make:resource ResidentResource

#Cherry-pick migration
# php artisan migrate --path=/database/migrations/2024_09_11_132738_create_residents_table.php

################################################################
prompt: 

generate a BlotterType model with controller, repository, repositoryinterface, resource, requests, provider & sample postman request

model properties:
type
description
category


## for debugging
cd /storage/logs
sudo truncate -s 0 laravel.log
less laravel.log

php artisan optimize:clear
php artisan route:clear
php artisan config:cache


## TODOs:
- add `student_count` in school table
- add `status`, `beneficiaries_count` in program table

Todays Milestones:
- fix the retrieval of single incident details ✅
- activity-log entity CRUD ✅
- integration of activity-log to user actionsrojectRequesr, UpdateProjectRequest etc.),
Resource, 





Project Title: Performance Management Information System website (PMIS) for the Department of Agriculture
Purpose: 
To create an interactive dashboard and user-friendly website for the Department of Agriculture that provides:
Progress reports for every department with visual data representation.
Real-time project tracking tools.
An accessible platform for stakeholders and the public to monitor agricultural initiatives.
Technology Stack:
Backend API Service: PHP with Laravel Framework for core business logic and APIs.
Database: MySQL 
Hosting: AWS Cloud (leveraging EC2 for virtual servers and Docker for   containerization, ensuring scalability and a microservices-ready architecture).
Security & Compliance: 
Data encryption to protect sensitive information.

Design and implement REST APIs to efficiently handle business logic and data processing
Database Design & Integration: Develop a well-structured MySQL database schema and ensure seamless integration for optimal data management and retrieval.

Role-Based Access Control (RBAC):
Public View: Provides limited access, displaying only essential information such as project name, brief description, and status.
Internal View: Grants authorized users access to detailed project information, including budget, team members, and timelines.
Audit Logs - Maintain detailed logs of user activities to ensure security, accountability, and traceability
Frontend Development (Angular Framework)
A dynamic news ticker for recent updates and achievements.
Overview of key statistics (e.g., total projects, active initiatives, funds allocated).
Data visualization integration (D3.js, PowerBI)
Hero banner showcasing major initiatives
Infographic of national agricultural performance (e.g., crop yields, livestock numbers).
Project Tracker & Reports Module Development
Gantt charts, filters, and status management, timelines and milestones.
Quick links to project tracker and department progress reports.
Interactive dashboard with filters (department, project type, status).
Dedicated pages for each department (e.g., Crop Development, Fisheries, Irrigation).
Monthly/quarterly progress reports in graphical form (e.g., bar graphs, pie charts).
Goals and KPIs for each department.
Interactive graphs comparing historical and current data.
Color-coded project statuses (e.g., Green: On Track, Yellow: Delayed, Red: Critical).
Export to PDF/Excel
Data Visualization & Mapping
Charts, graphs and real-time performance comparison
Real-time crop production statistics.
Comparison of regional agricultural performance.
Funding distribution pie charts.
Interactive map showing project locations.
Heatmaps for regional productivity and challenges.
Reporting
Repository of annual reports, white papers, and policy documents.
Search and filter tools for easy access.
User Engagement Tools
Contact forms for feedback and inquiries.
Newsletter subscription for updates.
Social media integration.


build a laravel backend api service for the  Department of Agriculture
folowing the best and recomended coding design patterns in laravel like service-repository 
pattern with repositoryinterface. Use Request (ex. StoreProjectRequesr, UpdateProjectRequest etc.),
Resource, 

