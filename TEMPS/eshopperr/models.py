from django.contrib.auth.models import User
from django.db import models

from django.forms import ModelForm, TextInput
from product.models import Product


class ShopCart(models.Model):

    user = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)
    product = models.OneToOneField(Product, on_delete=models.SET_NULL, null=True)
    quantity = models.IntegerField()

    def __str__(self):
        return self.product

    @property
    def amount(self):
        return (self.quantity * self.product.price)


class ShopCartForm(ModelForm):
    class Meta:
        model = ShopCart
        fields = ['quantity']
        widgets = {
            'quantity': TextInput(attrs={'class': 'input', 'type': 'number', 'value': '1'}),
        }


class Order(models.Model):
    STATUS = (
        ('New', 'New'),
        ('Accepted', 'Accepted'),
        ('Preparing', 'Preparing'),
        ('OnShippping', 'OnShippping'),
        ('Completed', 'Completed'),
        ('Canceled', 'Canceled'),

    )

    user = models.ForeignKey(User, on_delete=models.CASCADE)
    name = models.CharField(max_length=10)
    surname = models.CharField(max_length=10)
    address = models.CharField(max_length=150)
    city = models.CharField(max_length=20)
    phone = models.CharField(max_length=20)
    total = models.FloatField()
    note = models.TextField(null=True, default="")
    status = models.CharField(choices=STATUS, default='New', max_length=15)
    create_at = models.DateTimeField(auto_now_add=True)
    update_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        return self.name


class OrderForm(ModelForm):
    class Meta:
        model = Order
        fields = ['name', 'surname', 'address', 'city', 'phone']
        widgets = {
            'name': TextInput(attrs={'class': 'text'}),
            'surname': TextInput(attrs={'class': 'text'}),
            'address': TextInput(attrs={'class': 'text'}),
            'city': TextInput(attrs={'class': 'text'}),
            'phone': TextInput(attrs={'class': 'text'}),
        }


class OrderDetail(models.Model):
    order = models.ForeignKey(Order, on_delete=models.CASCADE)
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    product = models.ForeignKey(Product, on_delete=models.CASCADE)
    quantity = models.IntegerField()  # floatfileddıbaşta
    price = models.FloatField()
    total = models.FloatField(default=0)
    create_at = models.DateTimeField(auto_now_add=True)
    update_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        return self.product


