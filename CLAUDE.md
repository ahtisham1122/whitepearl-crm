# White Pearl City Gwadar — Real Estate CRM

## Project
A web-based Real Estate CRM for managing plots, clients, bookings, 
installments, and payments. Built with the Duralux Bootstrap 5 admin 
template.

## Tech Stack
- Laravel 11 (PHP 8.3)
- MySQL
- Blade templating
- Bootstrap 5 (Duralux template — plain HTML/CSS/JS, jQuery, NO Tailwind, NO build tool)
- Will be hosted on Hostinger SHARED hosting (no Node runtime on server, no VPS)

## Code Standards (follow strictly)
- Thin controllers — business logic goes in Service classes
- Use Repositories where data logic is complex
- Reusable Blade components and partials — never duplicate layout code
- Organize routes by module
- Follow Laravel naming conventions
- Clean, well-organized folder structure
- Form Request classes for validation (not inline validation)
- Migrations + seeders for all tables

## Roles (spatie/laravel-permission)
Super Admin, Admin, Manager, Sales, Accounts, Data Entry, Viewer

## Business Rules
- Payment methods = FIXED dropdown: Cash, Bank Transfer, Cheque, JazzCash, EasyPaisa
- When a payment is recorded against an installment: auto-mark it Paid and 
  recalculate the client's outstanding balance
- Partial payment on an installment = status "Partially Paid" until fully covered
- NO payment gateway, NO plot transfer module
- Plot statuses: Available, Reserved, Sold, Cancelled

## How to explain things to me
- Use very simple, plain English (explain like I'm a beginner)
- No fancy or dramatic words
- Tell me exactly what commands to run and what each does

## Build Order
1. Foundation: auth + roles + Blade layout from template
2. Blocks -> Plots -> Clients (+ nominee + documents)
3. Booking -> Installment Plans -> Payments -> Receipts
4. Plot Map -> Dashboard -> Reports -> Notifications -> Activity Logs

## Deferred (do NOT build yet)
Customer Portal, Mobile App, WhatsApp/SMS/Email notifications