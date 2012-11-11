set :application, "Lacus"
set :domain,      "coolery.com"
set :deploy_to,   "/var/www/vhosts/#{domain}"
set :app_path,    "app"
set :web_path,    "web"

set :use_sudo,    false
set :user,        "cooleryc"

set :repository,  "git://github.com/Briareos/Lacus.git"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set  :keep_releases,  3

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

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

after "deploy:setup", "upload_parameters"