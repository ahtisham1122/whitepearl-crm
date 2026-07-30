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

## Teaching Mode (IMPORTANT — I am learning from this project)
I want to LEARN, not just receive code. Every session, follow these:

1. After building any module or feature, explain in simple plain English:
   - What each main file does
   - Why it's in that folder
   - How the files connect to each other
2. Explain like you're teaching a beginner. No fancy words.
3. For important logic (especially payments and installments), explain the 
   "why" behind the approach, not just the "what".
4. When you introduce a new Laravel concept (migration, model, controller, 
   route, view, service, etc.), give me a one-line beginner definition the 
   first time it appears.
5. Keep code clean and readable — favor clarity over clever tricks, since I 
   need to understand it.
6. At the end of each module, give me 2-3 short "what you learned today" 
   bullet points I can save to my notes.

   ## Planned: Client Portal (BUILD LAST, after payments exist)
- When admin adds a client, also create a client login account.
- Client logs in with their client ID or email + a PASSWORD (password is 
  required for security — ID alone is not safe).
- Client portal is VIEW-ONLY. Client can see ONLY their own data:
  - Their plot(s) and which block
  - Total price, amount paid, amount remaining
  - Their installment schedule and payment history / receipts
- Client can NEVER see or access any other client's data. This must be 
  tested carefully (it's private financial info).
- Do NOT build this until Installments, Payments, and Receipts are done.

## Planned: Bulk Data Import (BUILD near the end, before going live)
- Client has hundreds of existing records (clients, plots, bookings) in 
  Excel/spreadsheets.
- Build an Excel/CSV import feature so this real data can be loaded in bulk 
  (not hand-typed).
- Likely need import for: plots, clients, and possibly bookings.
- Use a well-tested Laravel Excel import approach.
- Do this near the end, after core modules work, using the client's real 
  spreadsheet format.
   
   ## Planned: Plot Transfer (re-added — build later)
- A client who owns a plot can sell/transfer it to a NEW client.
- Record: old owner, new owner, transfer date, transfer charges, remarks.
- Keep transfer history on the plot.
- Update ownership so the plot now belongs to the new client.
- Build after bulk import, before or around deployment.