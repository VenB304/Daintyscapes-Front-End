CALL add_buyer('Kusu', 'gaki', 'kusu', '$2y$10$ysnGZt/sCBerBI2P6KX1dOWMevfPPZ8iAuZQpf9uPmm2SWTwjI5Ue', 'kusugaki@gmail.com', '09386254683', 'Philippines', 'Santa Clara', 'Totod', '23', '3746');
CALL debug_change_buyer_into_admin('1', 'admin');

CALL add_buyer('Kusu', 'gaki', 'kuso', '$2y$10$ysnGZt/sCBerBI2P6KX1dOWMevfPPZ8iAuZQpf9uPmm2SWTwjI5Ue', 'kusogaki@gmail.com', '09386254684', 'Philippines', 'Santa Clara', 'Totod', '23', '3746');
CALL debug_change_buyer_into_seller('2', 'seller');

CALL add_buyer('Kusu', 'gaki', 'buyer', '$2y$10$ysnGZt/sCBerBI2P6KX1dOWMevfPPZ8iAuZQpf9uPmm2SWTwjI5Ue', 'kusagaki@gmail.com', '09386254685', 'Philippines', 'Santa Clara', 'Totod', '23', '3746');

CALL initialize_add_order_status();

CALL debug_show_buyer_details(); 



-- SELECT * FROM users;