server "app1.phpsw.uk",
  user: "ubuntu",
  roles: %w[app app1 web],
  ssh_options: {
    forward_agent: true,
    auth_methods: %w[publickey]
  }

server "app2.phpsw.uk",
  user: "ubuntu",
  roles: %w[app app2 web],
  ssh_options: {
    forward_agent: true,
    auth_methods: %w[publickey]
  }
