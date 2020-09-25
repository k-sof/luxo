variable "digitalocean_token"  {
  type = string
}

variable "datadog_token"  {
  type = string
}

data "helm_repository" "stable" {
  name = "stable"
  url  = "https://kubernetes-charts.storage.googleapis.com"
}

provider "digitalocean" {
  token = var.digitalocean_token
}

resource "digitalocean_kubernetes_cluster" "luxo" {
  name    = "luxo"
  region  = "lon1"
  version = "1.15.9-do.2"

  node_pool {
    name       = "foobar"
    size       = "s-1vcpu-2gb"
    node_count = 1
  }
}

provider "kubernetes" {
  load_config_file = false
  host  = digitalocean_kubernetes_cluster.luxo.endpoint
  token = digitalocean_kubernetes_cluster.luxo.kube_config[0].token
  cluster_ca_certificate = base64decode(
    digitalocean_kubernetes_cluster.luxo.kube_config[0].cluster_ca_certificate
  )
}

provider "helm" {
  kubernetes {
    load_config_file = false
    host  = digitalocean_kubernetes_cluster.luxo.endpoint
    token = digitalocean_kubernetes_cluster.luxo.kube_config[0].token
    cluster_ca_certificate = base64decode(
    digitalocean_kubernetes_cluster.luxo.kube_config[0].cluster_ca_certificate
    )
  }
}

resource "helm_release" "datadog" {
  name       = "datadog"
  repository = data.helm_repository.stable.metadata[0].name
  chart      = "datadog"

  set {
    name  = "datadog.apiKey"
    value = var.datadog_token
  }
}
