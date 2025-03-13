echo "Seeding local database, make sure you have the 'mysql' cli installed..."
echo "This assumes you are located at the root project dir."

mysql -h 127.0.0.1 -u root -ppassword cse442_2025_spring_team_v_db < tests/Support/Data/dump.sql
