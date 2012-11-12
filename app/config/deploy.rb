set :application, "Lacus"
set :domain,      "coolery.com"
set :deploy_to,   "/var/www/vhosts/#{domain}"
set :app_path,    "app"

set :use_sudo,    false
set :user,        "cooleryc"
set :server_user  "apache"

set :repository,  "git://github.com/Briareos/Lacus.git"
set :scm,         :git
set :scm_verbose, true

set :model_manager, "doctrine"
set :deploy_via,  :remote_cache

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set  :keep_releases,  3

#logger.level = Logger::MAX_LEVEL

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,   [app_path + "/logs", web_path + "/uploads", "vendor"]
set :use_composer, true

task :upload_parameters do
  shared_path = deploy_to + "/shared"
  origin_file = "app/config/parameters.yml.dist"
  destination_file = shared_path + "/app/config/parameters.yml"

  try_sudo "mkdir -p #{File.dirname(destination_file)}"
  top.upload(origin_file, destination_file)
end

task :make_cache_writable do
  current_path = deploy_to + "/current"
  try_sudo "setfacl -R -m u:#{server_user}:rwx -m u:#{user}:rwx current/app/cache"
  try_sudo "setfacl -dR -m u:#{server_user}:rwx -m u:#{user}:rwx current/app/cache"
end

task :make_uploads_writable do
  try_sudo "setfacl -R -m u:#{server_user}:rwx -m u:#{user}:rwx shared/app/logs"
  try_sudo "setfacl -dR -m u:#{server_user}:rwx -m u:#{user}:rwx shared/app/logs"
end

task :make_uploads_writable do
  current_path = deploy_to + "/current"
  try_sudo "setfacl -R -m u:#{server_user}:rwx -m u:#{user}:rwx shared/web/uploads"
  try_sudo "setfacl -dR -m u:#{server_user}:rwx -m u:#{user}:rwx shared/web/uploads"
end

after "deploy:setup", "upload_parameters"
after "deploy", "make_cache_writable"

# setfacl -R -m u:apache:rwx -m u:cooleryc:rwx current/app/cache shared/app/logs shared/web/uploads
# setfacl -dR -m u:apache:rwx -m u:cooleryc:rwx current/app/cache shared/app/logs shared/web/uploads